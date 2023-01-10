<?php

namespace App\Form\data;

use Symfony\Component\Validator\Constraints as Assert;


/**
 *  UserInfoData
 * @package App\Form\Data
 * cette classe sert just pour le mapping entre le formulaire type et model dédier
 * se modèle qui se compose de plusieurs entités
 */
class StepOneData
{

    /**
     * @Assert\NotBlank
     */
    private $address;

    /**
     * @var string|null
     */
    private $prospectReference;


    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     * @return StepOneData
     */
    public function setAddress($address): StepOneData
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProspectReference(): ?string
    {
        return $this->prospectReference;
    }

    /**
     * @param string|null $prospectReference
     */
    public function setProspectReference(?string $prospectReference): void
    {
        $this->prospectReference = $prospectReference;
    }






}