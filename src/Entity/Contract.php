<?php

namespace App\Entity;

use App\DBAL\Types\CivilityType;
use App\DBAL\Types\ContractExternalState;
use App\DBAL\Types\ContractInternalStateType;
use App\Repository\ContractRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\DBAL\Types\ContactType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Exception;
use Fresh\DoctrineEnumBundle\Validator\Constraints\Enum as FreshEnum;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContractRepository::class)
 * @HasLifecycleCallbacks
 * @UniqueEntity(
 *     fields={"reference"},
 *     message="Cette valeur existe déjà dans la liste.")
 *
 * TODO ajouter une classe trait pour mettre les fonctions admin dedans, plus de lisibilité
 */
class Contract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @FreshEnum(entity="App\DBAL\Types\ContractType")
     * @ORM\Column(type="string", length=25)
     */
    private $type;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @FreshEnum(entity="App\DBAL\Types\ContractInternalStateType")
     * Le statut d’avancement de contrat [en-cours, valider, payer, switcher ...]
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $status = ContractInternalStateType::DRAFT;

    /**
     * @var Prospect
     * @ORM\ManyToOne(targetEntity="App\Entity\Prospect", inversedBy="contract")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prospect;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;


    /**
     * @FreshEnum(entity="App\DBAL\Types\ContractInternalStateType")
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $internalState = ContractInternalStateType::DRAFT;

    /**
     * @FreshEnum(entity="App\DBAL\Types\ContractExternalState")
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $externalState;

    /**
     * @Assert\Unique
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $reference;

    /**
     * La date de validation du contrat par l’agent ou le prospect
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * La date de signature effective
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $signedAt;

    /**
     * L'ip de signataire
     * @ORM\Column(type="string", nullable=true)
     */
    private $signatoryIp;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     */
    private $signedBy;


    /**
     * @var string|null
     * @ORM\Column(
     *     type="text",
     *     nullable=true,
     *     options={
     *          "comment"="Une colonne à remplir quand on manipule la DB à la main
     *          ou quand on apporte une modification hors parcours aux dossiers"
     *      }
     * )
     */
    private $observation;


    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $chosenOfferCode;


    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true,
     *     options={
     *          "comment"="La date de début d'effet, c'est la date ou le contrat passe à finalizer.
     * De cette date là ça commence le décompte delais de retracttaion"
     *      })
     */
    private $effectiveStartDate;


    /**
     * @ORM\Column(type="boolean")
     */
    private $isEstimForcedFromQc = false;


    /**
     * @ORM\Column(type="boolean",
     *     options={
     *     "comment":"Indique si le contrat est synchronisé dans le CRM ou pas encore",
     *     "default":"0"
     *      })
     */
    private $isSynchronizedWithCrm = false;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isAcceptCgv = false;

    /**
     * @ORM\ManyToOne(targetEntity=Partner::class, inversedBy="contract")
     */
    private $ohmPartner;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="contract")
     */
    private $businessAgent;

    /**
     * @ORM\Column(type="json", nullable=true)
     * Format du tableau json ['fieldName'=>['updatedAt'=>'oldValue']]
     * For the quotation update we make a new one, choose it and un_choose the old one
     * trace the operation by adding:
     *      [
     *          'quotation'=>['updatedAt'=>'idOldQuotation'],
     *          'chosenOfferCode'=>['updatedAt'=>'idOldQuotation']
     *      ]
     * and do the same in the quotation entity
     */
    private $updateHistory = [];

    /**
     * @ORM\OneToMany(targetEntity=Quotation::class, mappedBy="contract", orphanRemoval=true)
     */
    private $quotations;

    public function __construct(string $energy)
    {

        $this->signedBy = new ArrayCollection();
        $this->attachedDocuments = new ArrayCollection();
        $this->quotations = new ArrayCollection();

    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
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

   /* public function addQuotation(Quotation $quotation): self
    {
        if (!$this->quotations->contains($quotation)) {
            $this->quotations[] = $quotation;
            $quotation->setContractDraft($this);
        }

        return $this;
    }

    public function removeQuotation(Quotation $quotation): self
    {
        if ($this->quotations->contains($quotation)) {
            $this->quotations->removeElement($quotation);
            // set the owning side to null (unless already changed)
            if ($quotation->getContractDraft() === $this) {
                $quotation->setContractDraft(null);
            }
        }

        return $this;
    }*/

   /* public function removeQuotations(): self
    {
        foreach ($this->getQuotations() as $quotation) {
            $this->quotations->removeElement($quotation);
            // set the owning side to null (unless already changed)
            if ($quotation->getContractDraft() === $this) {
                $quotation->setContractDraft(null);
            }
        }

        return $this;
    }*/




    public function getInternalState(): ?string
    {
        return $this->internalState;
    }

    public function setInternalState(?string $internalState): self
    {
        $this->internalState = $internalState;

        return $this;
    }

    public function getExternalState(): ?string
    {
        return $this->externalState;
    }

    public function setExternalState(?string $externalState): self
    {
        $this->externalState = $externalState;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @PrePersist
     * @return $this
     */
    public function setReference(): self
    {
        return $this->preSetReference();
    }

    public function preSetReference(?bool $isForceRefresh = false, string $prefix = 'CT-'): self
    {
        $rand = (string)(time() * rand(99, 9999999));
        $randSec = (string)((date('H')*60*60) + (date('i') * 60) +date('s'));

        $this->reference = $this->reference && !$isForceRefresh
            ? $this->reference
            : $prefix . date('ymd') . substr($randSec . $rand, 0, 7);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * @return $this
     */
    public function setValidatedAt(): self
    {
        $this->validatedAt = new DateTime();

        return $this;
    }

    /**
     * @return $this
     */
    public function resetValidatedAt(): self
    {
        $this->validatedAt = null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSignedAt()
    {
        return $this->signedAt;
    }

    /**
     * @return $this
     */
    public function setSignedAt(): self
    {
        $this->signedAt = new DateTime();
        return $this;
    }

    /**
     * @return $this
     */
    public function resetSignedAt(): self
    {
        $this->signedAt = null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSignatoryIp()
    {
        return $this->signatoryIp;
    }

    /**
     * @param string|null $signatoryIp
     * @return $this
     */
    public function setSignatoryIp(?string $signatoryIp): self
    {
        $this->signatoryIp = $signatoryIp;
        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getSignedBy(): Collection
    {
        return $this->signedBy;
    }

    public function setSignedBy(User $signedBy): self
    {
        $this->signedBy = []; // initialiser la liste des signataires
        $this->signedBy[] = $signedBy;

        return $this;
    }

    public function addSignedBy(User $signedBy): self
    {
        if (!$this->signedBy->contains($signedBy)) {
            $this->signedBy[] = $signedBy;
        }

        return $this;
    }

    public function removeSignedBy(User $signedBy): self
    {
        if ($this->signedBy->contains($signedBy)) {
            $this->signedBy->removeElement($signedBy);
        }

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

    public function getProspectReference(): ?string
    {
        return $this->prospect->getReference();
    }

    /** Used in easy admin bundle */
    public function getContactPrincipal()
    {
        $principalContact = '';
        foreach ($this->prospect->getContacts() as $contact) {
            if ($contact->getType() === ContactType::PRINCIPAL) {
                $civility = CivilityType::getCsnChoices($contact->getCivility());
                if (is_array($civility)) {
                    $civility = array_pop($civility);
                }

                $principalContact = $civility . ' '
                    . $contact->getFirstName() . ' ' . $contact->getLastName();
                break;
            }
        }

        return $principalContact;
    }

    /** Used in easy admin bundle */
    public function getTelPrincipal()
    {
        $tel = '';
        foreach ($this->prospect->getContacts() as $contact) {
            if ($contact->getType() === ContactType::PRINCIPAL) {
                $tel = $contact->getPhone();
                break;
            }
        }

        return $tel;
    }

    /** Used in easy admin bundle */
    public function getMailPrincipal()
    {
        $mail = '';
        foreach ($this->prospect->getContacts() as $contact) {
            if ($contact->getType() === ContactType::PRINCIPAL) {
                $mail = $contact->getMail();
                break;
            }
        }

        return $mail;
    }


    public function getChosenOfferCode(): ?string
    {
        return $this->chosenOfferCode;
    }

    public function setChosenOfferCode(?string $chosenOfferCode): self
    {
        $this->chosenOfferCode = $chosenOfferCode;

        return $this;
    }



    /**
     * @return DateTime|null
     */
    public function getEffectiveStartDate(): ?DateTime
    {
        return $this->effectiveStartDate;
    }

    /**
     * @param DateTime|null $effectiveStartDate
     * @return Contract
     */
    public function setEffectiveStartDate(): Contract
    {
        $this->effectiveStartDate = new DateTime();
        return $this;
    }

    /**
     * @param DateTime|null $effectiveStartDate
     * @return Contract
     */
    public function resetEffectiveStartDate(): Contract
    {
        $this->effectiveStartDate = null;
        return $this;
    }



    public function getChosenOfferName()
    {
        foreach ($this->getQuotations() as $quotation) {
            if ($quotation->getIsChosen()) {
                return $quotation->getOffers()->first()->getName();
            }
        }

        return null;
    }

    public function getChosenOffer()
    {
        foreach ($this->getQuotations() as $quotation) {
            if ($quotation->getIsChosen()) {
                return $quotation->getOffers()->first();
            }
        }

        return null;
    }

    public function getChosenQuotation()
    {
        foreach ($this->getQuotations() as $quotation) {
            if ($quotation->getIsChosen()) {
                return $quotation;
            }
        }

        return null;
    }

    public function isTerminated(): bool
    {
        return in_array($this->getStatus(), ContractExternalState::getTerminatedStatus());
    }

    public function getIsSynchronizedWithCrm(): ?bool
    {
        return $this->isSynchronizedWithCrm;
    }

    public function setIsSynchronizedWithCrm(bool $isSynchronizedWithCrm): self
    {
        $this->isSynchronizedWithCrm = $isSynchronizedWithCrm;

        return $this;
    }




    /**
     * @return bool
     */
    public function getIsAcceptCgv(): bool
    {
        return !!$this->isAcceptCgv;
    }

    /**
     * @param bool $isAcceptCgv
     * @return $this
     */
    public function setIsAcceptCgv(?bool $isAcceptCgv): self
    {
        $this->isAcceptCgv = !!$isAcceptCgv;
        return $this;
    }

    public function getOhmPartner(): ?Partner
    {
        return $this->ohmPartner;
    }

    public function setOhmPartner(?Partner $ohmPartner): self
    {
        $this->ohmPartner = $ohmPartner;

        return $this;
    }

    public function getBusinessAgent(): ?User
    {
        return $this->businessAgent;
    }

    public function setBusinessAgent(?User $businessAgent): self
    {
        $this->businessAgent = $businessAgent;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getRiskCriteria(): ?array
    {
        return $this->riskCriteria ?? [];
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

    /**
     * @return Collection<int, Quotation>
     */
    public function getQuotations(): Collection
    {
        return $this->quotations;
    }

    public function addQuotation(Quotation $quotation): self
    {
        if (!$this->quotations->contains($quotation)) {
            $this->quotations[] = $quotation;
            $quotation->setContract($this);
        }

        return $this;
    }

    public function removeQuotation(Quotation $quotation): self
    {
        if ($this->quotations->removeElement($quotation)) {
            // set the owning side to null (unless already changed)
            if ($quotation->getContract() === $this) {
                $quotation->setContract(null);
            }
        }

        return $this;
    }
}
