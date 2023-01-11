<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use function array_flip;

class CivilityType extends AbstractEnumType
{
    // OHM énergie title
    public const MONSIEUR = 'mr';
    public const MADAME = 'mme';
    public const MADEMOISELLE = 'mle';

    // HomeServe title
    public const HS_MONSIEUR = 'M.';
    public const HS_MADAME = 'Mme';
    public const HS_MADEMOISELLE = 'Mlle';
    public const HS_FAMILY = 'Famille';
    public const HS_MONSIEUR_ET_MADEMOISELLE = 'M. & Mlle';
    public const HS_MONSIEUR_ET_MADAME = 'M. & Mme';
    public const HS_SOCIETE = 'Sté';
    public const HS_SOCIETE_CIVILE_IMMOBILIER = 'Sci';

    // Haugazel Title
    public const HGZ_MONSIEUR = 'MO';
    public const HGZ_MADAME = 'MA';
    public const HGZ_MADEMOISELLE = 'ML';

    // SlimPay Title
    public const SLIM_PAY_MONSIEUR = 'Mr';
    public const SLIM_PAY_MADAME = 'Mrs';

    // Garanka title
    public const G_MONSIEUR = 'M.';
    public const G_MADAME = 'Mme';

    protected static array $choices = [
        self::MONSIEUR => 'Monsieur',
        self::MADAME => 'Madame',
    ];

    protected static $icones = [
        self::MONSIEUR => '',
        self::MADAME => '',
        self::MADEMOISELLE => '',
    ];

    protected static $csnChoices = [
        self::MONSIEUR => 'Mr',
        self::MADAME => 'Ms',
        self::MADEMOISELLE => 'Mle',
    ];

    protected static $hgzChoices = [
        self::MONSIEUR => self::HGZ_MONSIEUR,
        self::MADAME => self::HGZ_MADAME,
        self::MADEMOISELLE => self::HGZ_MADEMOISELLE,
    ];

    protected static $slimPayChoices = [
        self::MONSIEUR => self::SLIM_PAY_MONSIEUR,
        self::MADAME => self::SLIM_PAY_MADAME,
    ];
    protected static $homeServeChoices = [
        self::MONSIEUR => self::HS_MONSIEUR,
        self::MADAME => self::HS_MADAME,
    ];
    protected static $choicesForApiEstimation = [
        self::MONSIEUR => 'Monsieur',
        self::MADAME => 'Madame',
        '' => '',
    ];

    /**
     * @return array|string
     */
    public static function getSlimPayChoices($key = null)
    {
        return ($key && key_exists($key, static::$slimPayChoices))
            ? static::$slimPayChoices[$key]
            : static::$slimPayChoices;
    }

    /**
     * @return array|string
     */
    public static function getCsnChoices($key = null)
    {
        return ($key && key_exists($key, static::$csnChoices))
            ? static::$csnChoices[$key]
            : static::$csnChoices;
    }



}
