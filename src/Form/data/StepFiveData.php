<?php


namespace App\Form\data;

use Symfony\Component\Validator\Constraints as Assert;


/**
 *  StepFiveData
 * @package App\Form\data
 * cette classe sert just pour le mapping entre le formulaire type et model dédier
 * se modèle qui se compose de plusieurs entités
 */

class StepFiveData
{
    /**
     * @Assert\NotBlank
     */
    private $orientation;

    /**
     * @return mixed
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @param mixed $orientation
     */
    public function setOrientation($orientation): void
    {
        $this->orientation = $orientation;
    }


}