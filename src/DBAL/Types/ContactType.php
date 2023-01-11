<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use function array_flip;

class ContactType extends AbstractEnumType
{
    public const PRINCIPAL = 'principal';
    public const SECONDARY = 'secondary';

    protected static array $choices = [
        self::PRINCIPAL => 'Principale',
        self::SECONDARY => 'Secondaire',
    ];

    protected static $icones = [
        self::PRINCIPAL => '',
        self::SECONDARY => '',
    ];

    /**
     * @static
     *
     * @return array Icons for the ENUM field
     */
    public static function getIcones(): array
    {
        return array_flip(static::$icones);
    }
}
