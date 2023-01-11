<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class ContractFinalized extends Constraint
{
    const INVALID_CONTRACT_STATUS = '079d96h20-2c63-460c-87256-de810ds3217d2';
    const INVALID_CONTRACT = '079d96h20-2c63-460c-87256-de810ds6518d2';

    protected static $errorNames = [
        self::INVALID_CONTRACT_STATUS => 'INVALID_CONTRACT_STATUS',
        self::INVALID_CONTRACT => 'INVALID_CONTRACT',
    ];

    public $message = 'Veuillez choisir un contrat finalisé.';
    public $messageAlreadyDuplicatedContract = 'Le contrat est déjà dupliqué. Veuillez utiliser le nouveau contrat.';
    public $messageInvalidContract = 'Veuillez choisir un contrat valide.';

    public function __construct($options = null)
    {
        parent::__construct($options);
    }
}
