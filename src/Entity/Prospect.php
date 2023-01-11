<?php

namespace App\Entity;

use App\DBAL\Types\ContactStatusType;
use App\DBAL\Types\ContactType;
use App\Repository\ProspectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProspectRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Prospect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */

    private $id;

    /**
     * @ORM\Column(type="string")
     */

    private $reference;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Contact::class, mappedBy="prospect",  cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity=Address::class, mappedBy="prospect",  cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $addresses;

    /**
     * @ORM\OneToOne(targetEntity=Roof::class, mappedBy="prospect", cascade={"persist", "remove"})
     */
    private $roof;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $currentStep;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $callMeLater = false;


    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Contract",
     *     mappedBy="prospect",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY")
     */
    private $contracts;



    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->addresses = new ArrayCollection();
       /* $this->communicatedHistory = new ArrayCollection();
        $this->attachedDocuments = new ArrayCollection();*/
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreate(): ?\DateTime
    {
        return $this->dateCreate;
    }
    /**
     * @ORM\PrePersist
     */
    public function setDateCreate(): self
    {
         $this->dateCreate = new \DateTime();
        return $this;
    }


    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }


    /**
     * @ORM\PrePersist
     */
    public function setDefaultStatus(): self
    {
        $this->setStatus(1);

        return $this;
    }


    public static function generateProspectReference(): string
    {
        $rand = (string)(time() * rand(99, 9999999));
        $randSec = (string)((date('H')*60*60) + (date('i') * 60) +date('s'));
        return 'SO-' . date('ymd') . substr($randSec . $rand, 0, 7);
    }




    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     */
    public function setReference($reference): Prospect
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setProspect($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getProspect() === $this) {
                $address->setProspect(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setProspect($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            // set the owning side to null (unless already changed)
            if ($contact->getProspect() === $this) {
                $contact->setProspect(null);
            }
        }

        return $this;
    }



    /**
     * @return Contact|null
     */
    public function getPrincipalContact(): ?Contact
    {
        foreach ($this->getContacts() as $contact) {
            if ($contact->getType() === ContactType::PRINCIPAL && $contact->getStatus() === ContactStatusType::CONTACT_IS_ACTIVE) {
                return $contact;
            }
        }

        return null;
    }

    /**
     * For easy admin
     * @return string|null
     */
    public function getPrincipalContactMail(): ?string
    {
        $principalContact = $this->getPrincipalContact();

        return $principalContact ? $principalContact->getMail() : null;
    }

    /**
     * For easy admin
     * @return string|null
     */
    public function getPrincipalContactFirstName(): ?string
    {
        $principalContact = $this->getPrincipalContact();

        return $principalContact ? $principalContact->getFirstName() : null;
    }

    /**
     * For easy admin
     * @return string|null
     */
    public function getPrincipalContactLastName(): ?string
    {
        $principalContact = $this->getPrincipalContact();

        return $principalContact ? $principalContact->getLastName() : null;
    }

    public function getRoof(): ?Roof
    {
        return $this->roof;
    }

    public function setRoof(?Roof $roof): self
    {
        // unset the owning side of the relation if necessary
        if ($roof === null && $this->roof !== null) {
            $this->roof->setProspect(null);
        }

        // set the owning side of the relation if necessary
        if ($roof !== null && $roof->getProspect() !== $this) {
            $roof->setProspect($this);
        }

        $this->roof = $roof;

        return $this;
    }

    public function addContractDraft(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts[] = $contract;
            $contract->setProspect($this);
        }

        return $this;
    }

    public function removeContractDraft(Contract $contract): self
    {
        if ($this->contracts->contains($contract)) {
            $this->contracts->removeElement($contract);
            // set the owning side to null (unless already changed)
            if ($contract->getProspect() === $this) {
                $contract->setProspect(null);
            }
        }

        return $this;
    }


}
