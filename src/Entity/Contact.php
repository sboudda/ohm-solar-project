<?php

namespace App\Entity;

use App\DBAL\Types\ContactStatusType;
use App\DBAL\Types\ContactType;
use App\Repository\ClientRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as OhmAssert;
use App\DBAL\Types\CivilityType;
use Fresh\DoctrineEnumBundle\Validator\Constraints\Enum as FreshEnum;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prospect", inversedBy="contacts", cascade={"persist"})
     */
    private $prospect;

    /**
     * @Assert\NotBlank
     * @OhmAssert\ContainsWord
     * @ORM\Column(type="string", length=100)
     */
    private $firstName;

    /**
     * @Assert\NotBlank
     * @OhmAssert\ContainsWord
     * @ORM\Column(type="string", length=100)
     */
    private $lastName;

    /**
     * @Assert\NotBlank
     * @OhmAssert\ContainsWord
     * @FreshEnum(entity="App\DBAL\Types\CivilityType")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone2;

    /**
     * @Assert\Email
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $mail;

    /**
     *
     * @FreshEnum(entity="App\DBAL\Types\ContactType")
     * @ORM\Column(type="string", length=40)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=150, nullable=true, options={"default"="not_verified"})
     */
    private $phoneValidationStatusFromApi;

    /**
     * @ORM\Column(type="string", length=150, nullable=true, options={"default"="not_verified"})
     */
    private $emailValidationStatusFromApi;

    /**
     * @var string|null
     * @FreshEnum(entity="App\DBAL\Types\ContactStatusType")
     * @ORM\Column(type="string", length=40, options={"default"="contact_is_active"})
     */
    private $status = ContactStatusType::CONTACT_IS_ACTIVE;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $updateHistory = []; // format du tableau json ['fieldName'=>['updatedAt'=>'oldValue']]

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, options={"default"="email"})
     */
    private $deliveryMode;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getProspect(): ?Prospect
    {
        return $this->prospect;
    }

    public function setProspect(?Prospect $prospect): self
    {
        $this->prospect = $prospect;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setPrincipal(): self
    {
        return $this->setType(ContactType::PRINCIPAL);
    }

    public function setSecondary(): self
    {
        return $this->setType(ContactType::SECONDARY);
    }

    public function getFullName()
    {
        return $this->firstName . '_' . $this->lastName;
    }

    /**
     * @return String|null
     */
    public function getPhoneValidationStatusFromApi(): ?string
    {
        return $this->phoneValidationStatusFromApi;
    }

    /**
     * @param mixed $phoneValidationStatusFromApi
     * @return Contact
     */
    public function setPhoneValidationStatusFromApi(?string $phoneValidationStatusFromApi): self
    {
        $this->phoneValidationStatusFromApi = $phoneValidationStatusFromApi;
        return $this;
    }

    /**
     * @return String|null
     */
    public function getEmailValidationStatusFromApi(): ?string
    {
        return $this->emailValidationStatusFromApi;
    }

    /**
     * @param mixed $emailValidationStatusFromApi
     * @return Contact
     */
    public function setEmailValidationStatusFromApi(?string $emailValidationStatusFromApi): self
    {
        $this->emailValidationStatusFromApi = $emailValidationStatusFromApi;
        return $this;
    }

    /**
     * @param string|null $status
     * @return Contact
     */
    public function setStatus(?string $status): Contact
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCreatedAt(): ?DateTimeInterface
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

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @PrePersist
     * @PreUpdate
     * @return $this
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new DateTime();

        return $this;
    }

    public function getUpdateHistory(): ?array
    {
        return $this->updateHistory;
    }

    public function setUpdateHistory(?array $updateHistory): self
    {
        $this->updateHistory = $updateHistory;

        return $this;
    }

    public function getDeliveryMode(): ?string
    {
        return $this->deliveryMode;
    }

    public function setDeliveryMode(?string $deliveryMode): self
    {
        $this->deliveryMode = $deliveryMode;

        return $this;
    }

}
