<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use function array_flip;

class ContractExternalState extends AbstractEnumType
{
    // les statuts liés à haulogy
    public const WAITING_PREPAY                     = 'waiting-prepay';
    public const WAITING_PREPAY_VAD                 = 'waiting-prepay-vad';
    public const WAITING_PREPAY_GAZ                 = 'waiting-prepay-gas';
    public const WAITING_PREPAY_GAZ_VAD             = 'waiting-prepay-gas-vad';
    public const WAITING_PREPAY_NO_SWITCH           = 'waiting-prepay-no-sw';
    public const WAITING_PREPAY_NO_SWITCH_VAD       = 'waiting-prepay-no-sw-vad';
    public const PREPAY_SWITCH_ASAP                 = 'prepay-no-sw';
    public const WAITING_FOURTEENTH_DAYS            = 'waiting-14d';
    public const TERMINATED_NEXT_EARTH              = 'terminatedNextEarth';
    public const SEND_TO_MKT_BY_PORTAL              = 'sendToMktByPortal';
    public const RECOVERY                           = 'Recovery';
    public const PP1_DONE_TO_TRANSFER_ODOO          = 'PP1doneToTransferOdoo';
    public const ABANDONED_TO_TRANSFER_ODOO         = 'abandonedToTransferOdoo';
    public const INITIALIZED                        = 'initialized';
    public const TO_VALIDATE                        = 'toValidate';
    public const WAITING                            = 'waiting';
    public const CANCELLED                          = 'Cancelled';
    public const ABANDONED = 'abandoned';
    public const ABANDONED_DUPLICATE = 'abandoned-duplicate';
    public const VALIDATION_REJECT = 'ValidationReject';
    public const REJECTED                           = 'Rejected';
    public const PREPAY_REJECTED                    = 'prepayRejected';
    public const SEND_TO_MKT                        = 'sendToMkt';
    public const ACCEPTED                           = 'Accepted';
    public const EFFECTIVE                          = 'effective';
    public const TERMINATED                         = 'terminated';
    public const UNKNOWN                            = 'unknown';

    protected static array $choices = [
        self::WAITING_PREPAY => 'en attente de paiement',
        self::WAITING_PREPAY_GAZ => 'en attente de paiement CTR GAZ',
        self::WAITING_PREPAY_GAZ_VAD => 'En attente de paiement CTR GAZ, destiné pour les ventes à domicile',
        self::WAITING_PREPAY_NO_SWITCH => 'En attente des index pour effectuer le pre-paiement',
        self::WAITING_PREPAY_NO_SWITCH_VAD => 'En attente des index pour effectuer le pre-paiement, aussi attente de délais légal pour les vente à domicile',
        self::PREPAY_SWITCH_ASAP => 'Switch ASAP',
        self::WAITING_FOURTEENTH_DAYS => 'Attente fin délai rétractation',
        self::TERMINATED_NEXT_EARTH => 'Terminé NextEarth',
        self::SEND_TO_MKT_BY_PORTAL => 'Demande hors HGZ',
        self::RECOVERY => 'Inactif (récupération)',
        self::PP1_DONE_TO_TRANSFER_ODOO => 'PP1 fait (à transférer à Odoo)',
        self::ABANDONED_TO_TRANSFER_ODOO => 'Abandonné (à transférer à Odoo)',
        self::TO_VALIDATE => 'A valider',
        self::INITIALIZED => 'Initialisé',
        self::WAITING => 'En attente d`intervention (manuel)',
        self::CANCELLED => 'Inactif (annulé par le marché)',
        self::ABANDONED => 'Abandon manuel',
        self::ABANDONED_DUPLICATE => 'Abandon des contrats doublons',
        self::VALIDATION_REJECT => 'Inactif (rejet validation interne)',
        self::REJECTED => 'Inactif (rejeté par le marché)',
        self::PREPAY_REJECTED => 'PP1 rejeté par la banque',
        self::SEND_TO_MKT => 'Envoyé au marché',
        self::ACCEPTED => 'Accepté',
        self::EFFECTIVE => 'Effectif',
        self::TERMINATED => 'Contrat qui a été actif, et qui est maintenant sorti du périmètre de livraison',
        self::UNKNOWN => 'Inconnue',
        self::WAITING_PREPAY_VAD => 'En attente de paiement, destiné pour les ventes à domicile',
    ];

    protected static $collapsedChoices = [
        self::WAITING_PREPAY => 'en attente de paiement',
        self::WAITING_PREPAY_GAZ => 'en attente de paiement CTR GAZ',
        self::WAITING_PREPAY_GAZ_VAD => 'En attente de paiement CTR GAZ, destiné pour les ventes à domicile',
        self::WAITING_PREPAY_NO_SWITCH => 'En attente des index pour effectuer le pre-paiement',
        self::WAITING_PREPAY_NO_SWITCH_VAD => 'En attente des index pour effectuer le pre-paiement, aussi attente de délais légal pour les vente à domicile',
        self::PREPAY_SWITCH_ASAP => 'Switch ASAP',
        self::WAITING_FOURTEENTH_DAYS => 'Attente fin délai rétractation',
        self::TERMINATED_NEXT_EARTH => 'Terminé NextEarth',
        self::SEND_TO_MKT_BY_PORTAL => 'Demande hors HGZ',
        self::RECOVERY => 'Inactif (récupération)',
        self::PP1_DONE_TO_TRANSFER_ODOO => 'PP1 fait (à transférer à Odoo)',
        self::ABANDONED_TO_TRANSFER_ODOO => 'Abandonné (à transférer à Odoo)',
        self::TO_VALIDATE => 'A valider',
        self::INITIALIZED => 'Initialisé',
        self::WAITING => 'En attente d`intervention (manuel)',
        self::CANCELLED => 'Inactif (annulé par le marché)',
        self::ABANDONED => 'Abandon manuel',
        self::VALIDATION_REJECT => 'Inactif (rejet validation interne)',
        self::REJECTED => 'Inactif (rejeté par le marché)',
        self::PREPAY_REJECTED => 'PP1 rejeté par la banque',
        self::SEND_TO_MKT => 'Envoyé au marché',
        self::ACCEPTED => 'Accepté',
        self::EFFECTIVE => 'Effectif',
        self::TERMINATED => 'Contrat qui a été actif, et qui est maintenant sorti du périmètre de livraison',
        self::UNKNOWN => 'Inconnue',
        self::WAITING_PREPAY_VAD => 'En attente de paiement, destiné pour les ventes à domicile',
    ];

    protected static $finalizedChoices = [
        self::WAITING_PREPAY => 'en attente de paiement',
        self::WAITING_PREPAY_GAZ => 'en attente de paiement CTR GAZ',
        self::WAITING_PREPAY_GAZ_VAD => 'En attente de paiement CTR GAZ, destiné pour les ventes à domicile',
        self::WAITING_PREPAY_NO_SWITCH => 'En attente des index pour effectuer le pre-paiement',
        self::WAITING_PREPAY_NO_SWITCH_VAD => 'En attente des index pour effectuer le pre-paiement, aussi attente de délais légal pour les vente à domicile',
        self::PREPAY_SWITCH_ASAP => 'Switch ASAP',
        self::WAITING_FOURTEENTH_DAYS => 'Attente fin délai rétractation',
        self::SEND_TO_MKT_BY_PORTAL => 'Demande hors HGZ',
        self::PP1_DONE_TO_TRANSFER_ODOO => 'PP1 fait (à transférer à Odoo)',
        self::TO_VALIDATE => 'A valider',
        self::INITIALIZED => 'Initialisé',
        self::WAITING => 'En attente d`intervention (manuel)',
        self::CANCELLED => 'Inactif (annulé par le marché)',
        self::SEND_TO_MKT => 'Envoyé au marché',
        self::ACCEPTED => 'Accepté',
        self::EFFECTIVE => 'Effectif',
        self::WAITING_PREPAY_VAD => 'En attente de paiement, destiné pour les ventes à domicile',
    ];

    protected static $terminatedChoices = [

        self::ABANDONED => 'Abandon manuel',
        self::TERMINATED => 'Contrat qui a été actif, et qui est maintenant sortide notre périmètre ',
    ];

    public static function getFinalizedChoices(): array
    {
        return array_flip(static::$finalizedChoices);
    }

    public static function getFinalizedChoicesForMailProcess(): array
    {
        $states = static::$finalizedChoices;
        unset($states[self::CANCELLED]);

        return array_flip($states);
    }

    public static function getTerminatedStatus(): array
    {
        return array_flip(static::$terminatedChoices);
    }
}
