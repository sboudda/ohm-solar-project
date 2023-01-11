<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class ContactStatusType extends AbstractEnumType
{
    public const CONTACT_IS_DEPRECATED = 'contact_is_deprecated';
    public const CONTACT_IS_ACTIVE = 'contact_is_active';

    protected static array $choices = [
        self::CONTACT_IS_DEPRECATED => 'Contact obsolète',
        self::CONTACT_IS_ACTIVE => 'Contact actif',
    ];
}
