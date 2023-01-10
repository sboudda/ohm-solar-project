<?php


namespace App\Form\data;

use Symfony\Component\Validator\Constraints as Assert;


/**
 *  StepThirdData
 * @package App\Form\data
 * cette classe sert just pour le mapping entre le formulaire type et model dédier
 * se modèle qui se compose de plusieurs entités
 */

class StepThirdData
{
    /**
     * @Assert\NotBlank
     */
    private $borderA;
    /**
     * @Assert\NotBlank
     */
    private $borderB;
    /**
     * @Assert\NotBlank
     */
    private $borderC;
    /**
     * @Assert\NotBlank
     */
    private $borderD;

    /**
     * @Assert\NotBlank
     */
    private $area;

    /**
     * @return mixed
     */
    public function getBorderA()
    {
        return $this->borderA;
    }

    /**
     * @return mixed
     */
    public function getBorderB()
    {
        return $this->borderB;
    }

    /**
     * @param mixed $borderB
     */
    public function setBorderB($borderB): void
    {
        $this->borderB = $borderB;
    }

    /**
     * @return mixed
     */
    public function getBorderC()
    {
        return $this->borderC;
    }

    /**
     * @param array $borderA
     */
    public function setBorderA($borderA): void
    {
        $this->borderA = $borderA;
    }

    /**
     * @param mixed $borderC
     */
    public function setBorderC($borderC): void
    {
        $this->borderC = $borderC;
    }

    /**
     * @return mixed
     */
    public function getBorderD()
    {
        return $this->borderD;
    }

    /**
     * @param mixed $borderD
     */
    public function setBorderD($borderD): void
    {
        $this->borderD = $borderD;
    }

    /**
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $area
     */
    public function setArea($area): void
    {
        $this->area = $area;
    }


}