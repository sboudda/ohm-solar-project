<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class ContainsWord extends Constraint
{
    public $message = 'La chaîne "{{ string }}" contient un caractère illégal : elle ne peut contenir que des lettres';

    public function validatedBy()
    {
        return static::class . 'Validator';
    }
}