<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class VerifiedProspect extends Constraint
{
    const INVALID_VALUE_ERROR = '0795Zd20-2d63-460c-87256-de8DSh8il3217d2';

    protected static $errorNames = [
        self::INVALID_VALUE_ERROR => 'INVALID_VALUE_ERROR',
    ];

    public $invalidMessage = 'Veuillez choisir une reference client valide {{ value }}';
    public $message = 'Veuillez choisir une reference client valide';

    /**
     * @return string
     */
    public function getInvalidMessage(): string
    {
        return $this->invalidMessage;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }


}
