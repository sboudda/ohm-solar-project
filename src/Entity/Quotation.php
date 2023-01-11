<?php

namespace App\Entity;

use App\Repository\QuotationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuotationRepository::class)
 */
class Quotation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $annualConsumption = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $saving = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isChosen = false;


    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="quotations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contract;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAnnualConsumption(): int
    {
        return $this->annualConsumption;
    }

    /**
     * @param int $annualConsumption
     */
    public function setAnnualConsumption(int $annualConsumption): void
    {
        $this->annualConsumption = $annualConsumption;
    }

    /**
     * @return int
     */
    public function getSaving(): int
    {
        return $this->saving;
    }

    /**
     * @param int $saving
     */
    public function setSaving(int $saving): void
    {
        $this->saving = $saving;
    }

    /**
     * @return bool
     */
    public function isChosen(): bool
    {
        return $this->isChosen;
    }

    /**
     * @param bool $isChosen
     */
    public function setIsChosen(bool $isChosen): void
    {
        $this->isChosen = $isChosen;
    }


}
