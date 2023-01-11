<?php

namespace App\DBAL\Types;

class ContractInternalStateType
{
    public const DRAFT = 'draft';// cette etat vas rester jusqu'a l'etape avoir un contrat solide,
    public const VALIDATED_BY_AGENT = 'validated_but_neither_signed_or_paid';// the agent validated the choice of the customer, and an email was sent for signing
    public const CONFIRMED_BY_AGENT = 'confirmed_by_agent';// it's the agent who sign this contract
    public const CONFIRMED_BY_WEB = 'confirmed_by_web';// not used for now
    public const ABANDONED_BY_WEB = 'abandoned_by_web';// not used for now
    public const ABANDONED_BY_AGENT = 'abandoned_by_agent'; // the agent abort the subscription
    public const SIGNED = 'signed';
    // ça veut dire on a un RUM → changement d'etat dès retour de SlimPay
    public const ORDER_CREATED = 'order_created'; // ordre de payement, cet état est juste après le retour de SlimPay
    public const FINALIZED = 'finalized';// SIGNED/CONFIRMED_BY_AGENT AND ORDER_CREATED
    public const PAYMENT_SCHEDULE_CREATED = 'payment_schedule_created'; // creation échéancier après signature, actuellement pris en charge par haulogy
    public const WAITING_FOR_FIRST_DRAWEL = 'waiting_for_first_drawel'; // en attente de premier prélèvement ,actuellement pris en charge par haulogy // TODO valider avec le meter to cash

    public const FRAUDULENT_IBAN = 'fraudulous_iban';
    public const WAITING_FOR_VALID_PDL = 'waiting_for_valid_pdl';
    public const WAITING_FOR_VALID_PCE = 'waiting_for_valid_pce';
    public const MISMATCH_WAITING_FOR_VALID_PDL = 'mismatch_waiting_for_valid_pdl';
    public const MISMATCH_WAITING_FOR_VALID_PCE = 'mismatch_waiting_for_valid_pce';
    public const NON_VALID_PRODUCT_ON_CURRENT_DATE = 'non_valid_product_on_current_date';
    // le contrat est finalisé par un VAD, on attend la validation par le service de control de ventes VAD
    public const WAITING_FOR_SALE_VALIDATION = 'waiting_for_sale_validation';
    public const SALE_IS_VALIDATED = 'sale_is_validated';
    public const SALE_IS_NOT_VALIDATED = 'sale_is_not_validated';
    public const ABANDONED_FOR_STAND_BY = 'abandoned_with_stand_by';
    public const ABANDONED_FOR_MOVING = 'abandoned_for_moving';
    public const WAITING_FOR_ABANDONING_THE_REPLACED_CONTRACT = 'waiting_for_abandoning_the_replaced_contract';

    public const TERMINATED_ON_PROSPECT_DEMAND = 'terminated_on_prospect_demand';

    public const INVALID_DELIVERY_POINT_SEGMENT = 'invalid_delivery_point_segment';
    public const INVALID_QUOTATION_NEGATIVE_MONTHLY_PAYMENT = 'invalid_quotation_negative_monthly_payment';
    public const INVALID_QUOTATION_NO_CHOSEN_OFFER = 'invalid_quotation_no_chosen_offer';
    public const INVALID_START_DATE_AGAINST_OFFER_DUE_DATE = 'invalid_start_date_against_offer_due_date';

    protected static array $choices = [
        self::DRAFT => 'Brouillon',
        self::ORDER_CREATED => 'Création du l\'ordre du payement',
        self::VALIDATED_BY_AGENT => 'Validé(Ni signé ni payé)',
        self::ABANDONED_BY_WEB => 'Abandonné par le client ',
        self::ABANDONED_BY_AGENT => 'Abandonné par l\'agent ',
        self::SIGNED => 'Signé',
        self::FINALIZED => 'Parcours finalisé',
        self::PAYMENT_SCHEDULE_CREATED => 'Échéancier crée',
        self::WAITING_FOR_FIRST_DRAWEL => 'En attente de premier prélèvement',

        // Ooops ! Nous avons détecté une erreur sur votre moyen de paiement et nous ne pouvons donc pas finaliser votre souscription
        self::FRAUDULENT_IBAN => 'Iban frauduleux', // Message à introduire dans le cas d'un iban frauduleux, dans la partie check iban
        self::WAITING_FOR_VALID_PDL => 'En attente du PDL valide',
        self::WAITING_FOR_VALID_PCE => 'En attente du PCE valide',
        self::MISMATCH_WAITING_FOR_VALID_PCE => 'En attente du PCE valide suite à un mismatch',
        self::MISMATCH_WAITING_FOR_VALID_PDL => 'En attente du PDL valide suite à un mismatch',
        self::NON_VALID_PRODUCT_ON_CURRENT_DATE => 'Offre non valide à cette date(non plus commercialiser)',
        self::INVALID_QUOTATION_NO_CHOSEN_OFFER => 'Problème de detection de l\'offre choisis',
        self::INVALID_DELIVERY_POINT_SEGMENT => 'Segment invalide du point de livraison',
        self::INVALID_QUOTATION_NEGATIVE_MONTHLY_PAYMENT => 'Estimation invalide',
        self::INVALID_START_DATE_AGAINST_OFFER_DUE_DATE => 'Date de début du contrat dépasse la date de validité de l\'offre',

        self::WAITING_FOR_SALE_VALIDATION => 'En attente de la validation de la vente',
        self::SALE_IS_VALIDATED => 'Vente validée par le contrôleur',
        self::SALE_IS_NOT_VALIDATED => 'Vente n\'est pas validée par le contrôleur',
        self::ABANDONED_FOR_STAND_BY => 'Contrat mis en attente',
        self::ABANDONED_FOR_MOVING => 'Contrat abandonner pour déménagement',
        self::WAITING_FOR_ABANDONING_THE_REPLACED_CONTRACT => 'Contrat en attente d\'abandon du contrat remplacé',

    ];

    protected static $validationChoices = [
        self::WAITING_FOR_SALE_VALIDATION => 'En attente de la validation de la vente',
        self::SALE_IS_VALIDATED => 'Vente validée par le contrôleur',
        self::SALE_IS_NOT_VALIDATED => 'Vente n\'est pas validée par le contrôleur',
    ];

    //cette variable contient les stauts qu'un contrat peut prendre
    protected static $contractStatusChoices = [
        self::DRAFT, // le contrat reste en brouillon jusqu'a l"etape recup and sign,

        // dans le parcours web, la validation du choix, la confirmation et signature sont dans le même écran,
        // ce n'est pas le cas dans le parcours phone, ils sont séparés
        self::VALIDATED_BY_AGENT, // dans l'étape recup and sign, on valide le contrat ou on abandonne
        self::ABANDONED_BY_AGENT,
        self::ABANDONED_BY_WEB,

        /**
         * Après l'étape recup, si on valide, on doit confirmer et tracker qui à confirmer
         */
        self::CONFIRMED_BY_AGENT,
        self::CONFIRMED_BY_WEB,

        /**
         * Parcours phone : après la confirmation de la signature
         * parcours web : après la validation (valider mon contrat ou après click sur lien d'email envoyer pour conirmer signature)
         */
        self::SIGNED,

        /**
         * Cet état est dès la reception de retour de slimpay
         */
        self::ORDER_CREATED,

        // après cette étape, on touve les étapes haulogy,
        // si tout est bon :
        //      - parcours web, statut pour transmettre vers haulogy est validated
        //      - statut phone, envoie des données sur plusieurs étapes, donc le statut est toValidate
        self::FINALIZED,
    ];

    protected static $notFinalizedChoices = [
        self::DRAFT,
        self::ORDER_CREATED,
        self::CONFIRMED_BY_AGENT,
        self::VALIDATED_BY_AGENT,
    ];

    protected static $finalizedChoices = [
        self::FINALIZED,
        //todo ajouter les autres statuts finalisés
    ];

    public static function getValidationChoices(): array
    {
        return array_flip(static::$validationChoices);
    }

    public static function getNotFinalizedChoices(): array
    {
        return array_flip(static::$notFinalizedChoices);
    }

    public static function getFinalizedChoices(): array
    {
        return array_flip(static::$finalizedChoices);
    }
}