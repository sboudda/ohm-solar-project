<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\OneToMany(targetEntity=Prospect::class, mappedBy="client")
     */
    private $Prospect;

    /**
     * @ORM\OneToMany(targetEntity=Address::class, mappedBy="client")
     */
    private $Address;

    public function __construct()
    {
        $this->addressId = new ArrayCollection();
        $this->Prospect = new ArrayCollection();
        $this->Address = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }



    /**
     * @return Collection<int, Prospect>
     */
    public function getProspect(): Collection
    {
        return $this->Prospect;
    }

    public function addProspect(Prospect $prospect): self
    {
        if (!$this->Prospect->contains($prospect)) {
            $this->Prospect[] = $prospect;
            $prospect->setClient($this);
        }

        return $this;
    }

    public function removeProspect(Prospect $prospect): self
    {
        if ($this->Prospect->removeElement($prospect)) {
            // set the owning side to null (unless already changed)
            if ($prospect->getClient() === $this) {
                $prospect->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddress(): Collection
    {
        return $this->Address;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->Address->contains($address)) {
            $this->Address[] = $address;
            $address->setClient($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->Address->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getClient() === $this) {
                $address->setClient(null);
            }
        }

        return $this;
    }
}
