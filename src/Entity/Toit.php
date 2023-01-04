<?php

namespace App\Entity;

use App\Repository\ToitRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ToitRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Toit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $declinaison;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $orientation;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $area;

    /**
     * @ORM\OneToOne(targetEntity=Prospect::class, inversedBy="toit", cascade={"persist", "remove"})
     */
    private $prospect;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeclinaison(): ?string
    {
        return $this->declinaison;
    }

    public function setDeclinaison(?string $declinaison): self
    {
        $this->declinaison = $declinaison;

        return $this;
    }

    public function getOrientation(): ?string
    {
        return $this->orientation;
    }

    public function setOrientation(?string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }


    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): self
    {
        $this->area = $area;

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
}
