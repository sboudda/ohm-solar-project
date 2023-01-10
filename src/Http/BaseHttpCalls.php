<?php

namespace App\Http;

use App\Constant\Constant;
use App\Utilities\TraceJourneyHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client as httpClient;
use ReflectionClass;
use SimpleXMLElement;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class BaseHttpCalls
 * @package App\Http
 */
abstract class BaseHttpCalls
{
    const HAUGAZEL_GAS_MARKET_LABEL = 'NG';
    const HAUGAZEL_ELEC_MARKET_LABEL = 'EL';
    const REMOTE_ERROR_CODE_KEY_IN_RESPONSE = 'remoteErrorCode';
    const OAUTH_ENDPOINT = '/api/oauth2';
    /**
     * @var ContainerBagInterface
     */
    protected $containerBag;
    /**
     * @var TraceJourneyHandler
     */
    protected $logger;
    /**
     * @var httpClient
     */
    protected $httpClient;
    // this endpoint is on version 1
    protected $headers;
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    public function __construct(
        ContainerBagInterface $containerBag,
        SessionInterface      $session,
        TraceJourneyHandler   $logger,
        EntityManagerInterface $em=null)
    {
        $this->containerBag = $containerBag;
        $this->session = $session;
        $this->httpClient = new httpClient(['verify' => false]);
        $this->headers = [];
        $this->logger = $logger;
        $this->entityManager = $em;
    }

    abstract public function send($params = []);

    abstract public function init();

    public function getStatusCodeFromXml($headers)
    {
        preg_match("/HTTP\/\d\.\d\s*\K[\d]+/", $headers, $matches);
        return is_array($matches) && count($matches) > 0 ? (int)$matches[0] : Response::HTTP_VERSION_NOT_SUPPORTED; // la version de retour xml n'est pas supporté
    }

    public function xml2array($xml)
    {
        $array = (array)$xml;

        if (count($array) === 0) {
            return (string)$xml;
        }

        foreach ($array as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $array[$key] = $this->xml2array($value);
            } else {
                continue;
            }
        }

        return $array;
    }

    /**
     * @param array $dataArray
     * @param SimpleXMLElement $xmlData
     */
    public function array2xml(array $dataArray, SimpleXMLElement &$xmlData)
    {
        foreach ($dataArray as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; //dealing with <0/>..<n/> issues
                }

                $subNode = $xmlData->addChild($key);
                $this->array2xml($value, $subNode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * Retourne une chaine de caractère en se basant sur des random int
     * @param int $length
     * @param string|null $separator
     * @param int $slice
     */
    public function generateGuid(int $length, ?string $separator = '', int $slice = 1, bool $isAlphaNumeric = true)
    {
        if ($length > 19) {
            if ($slice < intval($length / 19)) {
                throw new Exception('Le nombre de morceau est insuffisant pour la longueur chercher');
            }
        }

        if (is_null($separator) || !in_array($separator, ['-', '_', ''])) {
            throw new Exception('Le séparateur est incorrect, laisser sa valeur à vide ou mettez un séparateur valide');
        }

        $array = [];
        if ($slice > 1) {
            $sliceLength = intval($length / $slice);

            for ($i = 0; $i < $slice; ++$i) {
                $array[$i] = substr(strval($this->getRandomValue($isAlphaNumeric)), 0, $sliceLength);
            }

            $randomValue = implode($separator, $array);
        } else {
            $randomValue = strval($this->getRandomValue($isAlphaNumeric));
        }

        return substr($randomValue, 0, $length);
    }

    private function getRandomValue(bool $isAlphaNumeric = true)
    {
        if ($isAlphaNumeric) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 19; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        } else {
            return random_int(95865, PHP_INT_MAX);
        }
    }

    function object_to_array_reflection($obj)
    {
        $rfc = new ReflectionClass(get_class($obj));
        $arr = [];

        foreach ($rfc->getProperties() as $prop) {
            $prop->setAccessible(true);
            $arr[$prop->getName()] = $prop->getValue($obj);
            $prop->setAccessible(false);
        }

        return $arr;
    }

    protected function withJsonHeaders()
    {
        $this->headers = array_merge($this->headers, ['Content-Type' => 'application/json']);
    }

    protected function withXmlHeaders()
    {
        $this->headers = array_merge($this->headers, ['Content-Type' => 'application/xml']);
    }

    protected function withAuthorizationHeaders()
    {
        $this->headers = array_merge($this->headers, [
            'Authorization' => $this->containerBag->get('haulogy.haugazel.api.oauth_type') . ' '
                . base64_encode(
                    $this->containerBag->get('haulogy.haugazel.api.login') . ':'
                    . $this->containerBag->get('haulogy.haugazel.api.password')
                )
        ]);
    }

    /**
     * @param string $login
     * @param string $password
     */
    protected function withBasicAuthorizationHeaders(string $login, string $password)
    {
        $this->headers = array_merge($this->headers, [
            'Authorization' => 'Basic ' . base64_encode($login . ':' . $password)
        ]);
    }

    /**
     * @param string $token
     */
    protected function withPrivateTokenAuthorizationHeaders(string $token)
    {
        $this->headers = array_merge($this->headers, [
            'Private-Token' => $token
        ]);
    }

    /**
     * @param string $login
     * @param string $password
     */
    protected function withBasicAuthorizationTokenHeaders(string $token)
    {
        $this->headers = array_merge($this->headers, [
            'Authorization' => 'Bearer ' . $token
        ]);
    }

    protected function logTheCallDuration(?string $className, $startTime, $endTime)
    {
        if ($this->containerBag->get('trace_web_service_timing') === false) {
            return;
        }

        $prospectReference = 'UNKNOWN';
        if ($this->session->has('prospect_reference_in_current_journey')) {
            $prospectReference = $this->session->get('prospect_reference_in_current_journey');
        }

        $this->logger->notice(
            Constant::HTTP_BASE_CALLS_MANAGEMENT,
            sprintf('Call WS %s for prospect journey %s: duration %s', $className, $prospectReference, $endTime - $startTime)
        );
    }

    protected function array2Object($array)
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->array2Object($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    protected function withSSLVerification(bool $status = true)
    {
        $this->headers = array_merge($this->headers, ['verify' => $status]);
    }

    /**
     * @param mixed $data
     * @param mixed $default
     * @param array $keys
     * @return mixed|string|void
     */
    protected function getValueFromArray($data , array $keys, $default = '')
    {
        foreach ($keys as $index => $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                if (is_array($data[$key]) && !empty($keys)) {
                    unset($keys[$index]);
                    if (!empty($keys)) {
                         return $this->getValueFromArray($data[$key], $keys);
                    }
                }
            }

            return is_array($data) && ! empty($data[$key]) ? $data[$key] : $default ;
        }
    }
}
