<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostalReferentielRepository;

/**
 * @ORM\Entity(repositoryClass=PostalReferentielRepository::class)
 */
class PostalReferentiel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $codeCommuneInsee;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $codePostal;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $nomCommune;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $libelleAcheminement;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $consumptionZone;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $coveredByHomeServe = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeCommuneInsee(): ?string
    {
        return $this->codeCommuneInsee;
    }

    public function setCodeCommuneInsee(?string $codeCommuneInsee): self
    {
        $this->codeCommuneInsee = $codeCommuneInsee;

        return $this;
    }

    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    public function setCodePostal(?int $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getNomCommune(): ?string
    {
        return $this->nomCommune;
    }

    public function setNomCommune(?string $nomCommune): self
    {
        $this->nomCommune = $nomCommune;

        return $this;
    }

    public function getLibelleAcheminement(): ?string
    {
        return $this->libelleAcheminement;
    }

    public function setLibelleAcheminement(?string $libelleAcheminement): self
    {
        $this->libelleAcheminement = $libelleAcheminement;

        return $this;
    }

    public function getConsumptionZone(): ?int
    {
        return $this->consumptionZone;
    }

    public function setConsumptionZone(?int $consumptionZone): self
    {
        $this->consumptionZone = $consumptionZone;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCoveredByHomeServe(): bool
    {
        return !!$this->coveredByHomeServe;
    }

    /**
     * @param bool $coveredByHomeServe
     * @return PostalReferentiel
     */
    public function setCoveredByHomeServe(bool $coveredByHomeServe): PostalReferentiel
    {
        $this->coveredByHomeServe = $coveredByHomeServe;
        return $this;
    }
}
