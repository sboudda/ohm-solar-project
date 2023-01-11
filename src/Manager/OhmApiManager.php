<?php

namespace App\Manager;

use App\Constant\Constant;
use App\Http\BaseHttpCalls;
use App\Utilities\TraceJourneyHandler;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

class OhmApiManager extends BaseHttpCalls
{
    /**
     * constant for the relative url of the api end point
     */
    const RELATIVE_URL = '/api/';

    private $password;

    private $username;

    private $token;

    private $endPoint;

    private $response;

    public function __construct(ContainerBagInterface $containerBag, SessionInterface $session, TraceJourneyHandler $logger, EntityManagerInterface $em = null)
    {
        parent::__construct($containerBag, $session, $logger, $em);
        $this->username = $this->containerBag->get('ohm.username');
        $this->password = $this->containerBag->get('ohm.password');
        $this->token = null;

    }

    /**
     * Function to return the estimation given a string pdl
     * @param $pdl
     * @return mixed
     */
    public function getEstimationbyPdl($pdl)
    {
        $this->getToken();
        $estimation = false;
        $this->setAuthorisationHeaders();
        $this->setEndPoint("consulter-mesure");
        // set the standard variables
        /**
         * @Todo either move the constants in configs as they won't change except the pdl until further notice
         */
        $queryParams = [
            'deliveryPoint' => $pdl,
            'predictionLevel' => 1,
            'contractType' => 'changement_de_fournisseur',
            'energy' => 'elec_energy',
            'token' => 'no_token',
            'salt' => 'no_salt'
        ];
        $response = $this->send(null, $queryParams);
        $this->handleResponse($response);

        return
            ($this->response &&
                isset($this->response['body']['totalEstimatedMeasure'])) ?
                $this->response['body']['totalEstimatedMeasure'] : false;

    }

    /**
     * Function to return the token for future use.
     * @return mixed
     */
    public function getToken()
    {
        $this->init();
        //the endpoint for token is "login_check"
        $this->setEndPoint("login_check");
        $response = $this->send(['username' => $this->username, 'password' => $this->password]);
        $this->handleResponse($response);

        return $this->response ? $this->setToken($this->response['token']) : false;
    }

    /**
     * Function to initialise the headers
     * @return $this
     */
    public function init()
    {
        $this->withJsonHeaders();
        return $this;
    }

    /**
     * Main function to fetch the data from the endpoint
     * @param $params
     * @param $queryParams
     * @return array
     * @throws GuzzleException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function send($params = [], $queryParams = [])
    {
        $uri = $this->containerBag->get('ohm.base_url') . self::RELATIVE_URL;
        if ($this->getEndPoint()) {
            $uri .= $this->getEndPoint();
        }
        try {
            $startTime = microtime(true);
            $response = $this->httpClient->request(Request::METHOD_POST, $uri, [
                'body' => json_encode(!empty($params) ? $params : null),
                'headers' => $this->headers,
                'allow_redirects' => false,
                'query' => $queryParams,
            ]);
            $endTime = microtime(true);
            $this->logTheCallDuration(get_class($this), $startTime, $endTime);

            return [
                'code' => $response->getStatusCode(),
                'body' => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception $exception) {
            return [
                'code' => $exception->getCode(),
                'body' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Function to set the Endpoint
     * @param $endPointPart
     * @return void
     */
    private function setEndPoint($endPointPart): void
    {
        $this->endPoint = $endPointPart;
    }

    /**
     * Function to get the Endpoint
     * @return string
     */
    private function getEndPoint(): string
    {
        return $this->endPoint;
    }

    /**
     * Function to handle the response
     * @param $response
     * @return void
     */
    private function handleResponse($response)
    {
        if ($response['code'] == Response::HTTP_OK) {
            $this->response = $response['body'];
        } else {
            //an error happened so we put it in the log
            $context = Constant::API;
            $message = $response['body'];
            $refContract = null;
            $extra = "Error Code : " . $response['code'];
            $referenceClient = null;
            $this->logger->error($context, $message, $refContract, $extra, $referenceClient);
            $this->response = false;
        }
    }

    /**
     * Sets the Token property
     * @param $token
     * @return string
     */
    private function setToken($token): string
    {
        $this->token = $token;

        return $this->token;
    }

    /**
     * Function to set the headers for api calls which needs authorisation bearer
     * @return void
     */
    private function setAuthorisationHeaders()
    {
        $this->init();
        $this->withBasicAuthorizationTokenHeaders($this->getToken());
    }
}