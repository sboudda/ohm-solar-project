<?php

namespace App\Entity;

use App\Repository\ProspectRepository;
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
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="Prospect")
     */
    private $client;

    /**
     * @ORM\OneToOne(targetEntity=Toit::class, mappedBy="prospect", cascade={"persist", "remove"})
     */
    private $toit;


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


    public function getClient(): ?Client
    {
        return $this->client;
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

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getToit(): ?Toit
    {
        return $this->toit;
    }

    public function setToit(?Toit $toit): self
    {
        // unset the owning side of the relation if necessary
        if ($toit === null && $this->toit !== null) {
            $this->toit->setProspect(null);
        }

        // set the owning side of the relation if necessary
        if ($toit !== null && $toit->getProspect() !== $this) {
            $toit->setProspect($this);
        }

        $this->toit = $toit;

        return $this;
    }

}
