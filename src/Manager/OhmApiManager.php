<?php

namespace App\Manager;

use App\Http\BaseHttpCalls;
use App\Utilities\TraceJourneyHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

class OhmApiManager extends BaseHttpCalls
{
    const RELATIVE_URL = '/api/';

    /*
     * @var password
     */
    /**
     * @var username
     */
    private $username;
    private $password;
    /**
     * @var null
     */
    private $token;

    public function __construct(ContainerBagInterface $containerBag, SessionInterface $session, TraceJourneyHandler $logger, EntityManagerInterface $em = null)
    {
        parent::__construct($containerBag, $session, $logger, $em);
        $this->username = $this->containerBag->get('ohm.username');
        $this->password = $this->containerBag->get('ohm.password');
        $this->token = null;

    }

    /**
     * @return void
     */
    public function getToken()
    {

        $this->init();
        $response = $this->send(['username' => $this->username, 'password' => $this->password, 'action_path' => 'login_check']);
        if ($response['code'] == Response::HTTP_OK) {
            $this->token = $response['body']['token'];
        } else {

            throw new Exception('Une erreur est survenue lors de la recuperation du token');
        }

        return $this->token;
    }

    public function init()
    {
        $this->withJsonHeaders();
        return $this;
    }

    public function send($params = [])
    {
        $uri = $this->containerBag->get('ohm.base_url') . self::RELATIVE_URL;
        if (array_key_exists('action_path', $params) && isset($params['action_path'])) {
            $uri .= $params['action_path'];
        }
        try {
            $startTime = microtime(true);
            $response = $this->httpClient->request(Request::METHOD_POST, $uri, [
                'body' => json_encode(!empty($params) ? $params : null),
                'headers' => $this->headers,
                'allow_redirects' => false
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
}