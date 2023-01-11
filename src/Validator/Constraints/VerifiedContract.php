<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class VerifiedContract extends Constraint
{
    const INVALID_VALUE_ERROR = '0795hd20-2d63-460c-87256-de8DSh8il3217d2';

    protected static $errorNames = [
        self::INVALID_VALUE_ERROR => 'INVALID_VALUE_ERROR',
    ];

    public $message = 'Veuillez choisir une reference de contrat valide';

    public function validatedBy()
    {
        return static::class . 'Validator';
    }
}
