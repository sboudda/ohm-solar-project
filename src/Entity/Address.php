<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prospect", inversedBy="addresses", cascade={"persist"})
     */
    private $prospect;

    /**
     * @ORM\Column(type="integer", length=1, nullable=true)
     */
    private $favorite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $RawAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Town;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $geoCode = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $points = [];


    public function getId(): ?int
    {
        return $this->id;
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


    public function getRawAddress(): ?string
    {
        return $this->RawAddress;
    }

    public function setRawAddress(?string $RawAddress): self
    {
        $this->RawAddress = $RawAddress;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->Country;
    }

    public function setCountry(?string $Country): self
    {
        $this->Country = $Country;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->Town;
    }

    public function setTown(?string $Town): self
    {
        $this->Town = $Town;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getGeoCode(): ?array
    {
        return $this->geoCode;
    }

    public function setGeoCode(?array $geoCode): self
    {
        $this->geoCode = $geoCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFavorite()
    {
        return $this->favorite;
    }

    /**
     * @param mixed $favorite
     */
    public function setFavorite($favorite): void
    {
        $this->favorite = $favorite;
    }

    /**
     * @return array
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * @param array $points
     */
    public function setPoints(array $points): void
    {
        $this->points = $points;
    }


}
