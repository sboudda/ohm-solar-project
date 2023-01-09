<?php

namespace App\Entity;

use App\DBAL\Types\JourneyTracerContextType;
use App\DBAL\Types\JourneyType;
use App\Repository\LogSousDataRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Exception;

/**
 * @ORM\Entity(repositoryClass=LogSousDataRepository::class)
 * @ORM\Table(name="log_sous_journey")
 * @ORM\HasLifecycleCallbacks
 */
class LogSousData
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $reference;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $contractReference;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $journey;

    /**
     * @var string|null
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var string|null
     * @ORM\Column(name="context", type="string", nullable=true)
     */
    private $context;

    /**
     * @var int|null
     * vendor\monolog\monolog\src\Monolog\Logger.php
     * @ORM\Column(name="level", type="smallint", nullable=true)
     */
    private $level;

    /**
     * @var string|null
     * vendor\monolog\monolog\src\Monolog\Logger.php :: getLevelName()
     * @ORM\Column(name="level_name", type="string", length=50, nullable=true)
     */
    private $levelName;

    /**
     * @var string|null
     * @ORM\Column(name="extra", type="text", nullable=true)
     */
    private $extra;

    /**
     * @var string|null
     * @ORM\Column(name="ip_address", type="string", nullable=true)
     */
    private $ipAddress;

    /**
     * @var datetime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var int
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $user;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string|null $reference
     * @return LogSousData
     */
    public function setReference(?string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContractReference(): ?string
    {
        return $this->contractReference;
    }

    /**
     * @param string|null $contractReference
     * @return LogSousData
     */
    public function setContractReference(?string $contractReference): self
    {
        $this->contractReference = $contractReference;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJourney(): ?string
    {
        return $this->journey;
    }

    /**
     * @param string|null $journey
     * @return LogSousData
     */
    public function setJourney(?string $journey): self
    {
        $this->journey = $journey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     * @return LogSousData
     */
    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @param string|null $context
     * @return LogSousData
     */
    public function setContext(?string $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int|null $level
     * @return LogSousData
     */
    public function setLevel(?int $level): self
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLevelName(): ?string
    {
        return $this->levelName;
    }

    /**
     * @param string|null $levelName
     * @return LogSousData
     */
    public function setLevelName(?string $levelName): self
    {
        $this->levelName = $levelName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtra(): ?string
    {
        return $this->extra;
    }

    /**
     * @param string|null $extra
     * @return LogSousData
     */
    public function setExtra(?string $extra): LogSousData
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string|null $ipAddress
     * @return LogSousData
     */
    public function setIpAddress(?string $ipAddress): LogSousData
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }


    /**
     * @PrePersist
     * @return $this
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new DateTime();

        return $this;
    }

    /**
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @param int $user
     * @return LogSousData
     */
    public function setUser(?int $user): LogSousData
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Necessaire pour easy admin
     * @return string|null
     */
    public function getHumanizedExtraData(): ?string
    {
        try {
            return urldecode($this->extra);
        } catch (Exception $e) {
            return $this->extra;
        }
    }

    /**
     * Necessaire pour easy admin
     * @return string|null
     */
    public function getHumanizeMessage(): ?string
    {
        try {
            $res = '';
            $message = unserialize($this->message);
            if (is_array($message) && array_key_exists('code', $message)) {
                $res .= sprintf('Code : %d', $message['code']);
            }

            if (is_array($message) && array_key_exists('message', $message)) {
                $res .= PHP_EOL . sprintf('Message : %s', $message['message']);
            }

            if (is_array($message) && array_key_exists('body', $message)) {
                if (is_string($message['body'])) {
                    // format de retour général
                    $res .= PHP_EOL . sprintf('Extra Message : %s', $message['body']);
                } elseif (is_array($message['body']) && array_key_exists('message', $message['body'])) {
                    // format de retour haugazel
                    $res .= PHP_EOL . sprintf('Extra Message : %s', $message['body']['message']);
                } elseif (is_object($message['body']) && property_exists($message['body'], 'message')) {
                    // format de message de retour Cassandra
                    $res .= PHP_EOL . sprintf('Extra Message : %s', $message['body']->message);
                } else {
                    // format de retour non connue, TODO à detecter les defferents cas
                    $res .= PHP_EOL . sprintf('Extra Message : %s', serialize($message['body']));
                }
            }

            return $res;
        } catch (Exception $e) {
            return $this->message;
        }
    }

    /**
     * Utilie pour Easy admin
     * @return string|null
     */
    public function getHumanizeJourney(): ?string
    {
        if (!empty($this->journey)) {
            return JourneyType::getReadableValue($this->journey);
        }

        return '';
    }

    /**
     * Utilie pour Easy admin
     * @return string|null
     */
    public function getHumanizeContext(): ?string
    {
        if (!empty($this->context)) {
            return JourneyTracerContextType::getReadableValue($this->context);
        }

        return '';
    }
}