<?php

namespace App\Form\data;

use Symfony\Component\Validator\Constraints as Assert;


/**
 *  StepTwoData
 * @package App\Form\Data
 * cette classe sert just pour le mapping entre le formulaire type et model dédier
 * se modèle qui se compose de plusieurs entités
 */
class StepTwoData
{

    /**
     * @Assert\NotBlank
     */

    private $geoCodeLat;
    /**
     * @Assert\NotBlank
     */
    private $geoCodeLng;

    /**
     * @return mixed
     */
    public function getGeoCodeLat()
    {
        return $this->geoCodeLat;
    }

    /**
     * @param mixed $geoCodeLat
     */
    public function setGeoCodeLat($geoCodeLat): void
    {
        $this->geoCodeLat = $geoCodeLat;
    }

    /**
     * @return mixed
     */
    public function getGeoCodeLng()
    {
        return $this->geoCodeLng;
    }

    /**
     * @param mixed $geoCodeLng
     */
    public function setGeoCodeLng($geoCodeLng): void
    {
        $this->geoCodeLng = $geoCodeLng;
    }





}