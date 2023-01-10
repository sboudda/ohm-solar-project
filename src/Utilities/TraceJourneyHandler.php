<?php

namespace App\Utilities;

use App\Constant\Constant;
use App\Entity\LogSousData;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class TraceJourneyHandler
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var Request|null
     */
    private $request;
    /**
     * @var JourneyUtilities
     */
    private $journeyUtilities;
    /**
     * @var Security
     */
    private $security;

    /**
     * MonologDBHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager,
                                RequestStack           $requestStack,
                                Security  $security,
                                JourneyUtilities       $journeyUtilities)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->journeyUtilities = $journeyUtilities;
        $this->security = $security;
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function debug(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::DEBUG,
            Logger::getLevelName(Logger::DEBUG),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param int $level
     * @param string $levelName
     * @param string $context
     * @param string $message
     * @param string|null $journey
     * @param string|null $refClient
     * @param string|null $refContract
     * @param string|null $extra
     */
    public function addRecord(
        int     $level,
        string  $levelName,
        string  $context,
        string  $message,
        ?string $journey = null,
        ?string $refClient = null,
        ?string $refContract = null,
        ?string $extra = null
    )
    {
        $user = $this->security->getUser() ? $this->security->getUser() : null;
        $isValidUser = $user && ($user instanceof User);
        $logEntry = new LogSousData();
        $logEntry
            ->setLevel($level)
            ->setLevelName($levelName)
            ->setJourney($journey ?? JourneyType::WEB_JOURNEY)
            ->setContext($context)
            ->setReference($refClient)
            ->setContractReference($refContract)
            ->setMessage($message)
            ->setExtra($extra)
            ->setIpAddress($this->request ? $this->request->getClientIp() : 'server ip')
            ->setUser($isValidUser ? $user->getId() : null);

        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function info(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::INFO,
            Logger::getLevelName(Logger::INFO),
            $context ?? JourneyTracerContextType::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function notice(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::NOTICE,
            Logger::getLevelName(Logger::NOTICE),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function warning(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::WARNING,
            Logger::getLevelName(Logger::WARNING),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     */
    public function error(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::ERROR,
            Logger::getLevelName(Logger::ERROR),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function critical(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::CRITICAL,
            Logger::getLevelName(Logger::CRITICAL),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function alert(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::ALERT,
            Logger::getLevelName(Logger::ALERT),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * @param string|null $context
     * @param string|null $message
     * @param string|null $refContract
     * @param string|null $extra
     * @param string|null $referenceClient
     */
    public function emergency(
        ?string $context = null,
        ?string $message = null,
        ?string $refContract = null,
        ?string $extra = null,
        ?string $referenceClient = null
    )
    {
        $this->addRecord(
            Logger::EMERGENCY,
            Logger::getLevelName(Logger::EMERGENCY),
            $context ?? Constant::NAVIGATION,
            $message ?? $this->request->getRequestUri(),
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            $refContract ?? 'Undefined',
            $extra
        );
    }

    /**
     * This id for tracing all calls in all journeys
     */
    public function traceRequest(?string $referenceClient = null)
    {
        $this->addRecord(
            Logger::NOTICE,
            Logger::getLevelName(Logger::NOTICE),
            Constant::NAVIGATION . ($this->request ? '_' . $this->request->getMethod() : ''),
            $this->request ? $this->request->getRequestUri() : 'Undefined',
            $this->journeyUtilities->guessJourney(),
            $this->journeyUtilities->guessClientReference($referenceClient),
            'Undefined',
            $this->request ? urldecode($this->request->getContent()) : 'Undefined'
        );
    }
}
