<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class OhmPartnerConstraint  extends Constraint
{
    const INVALID_VALUE_ERROR = '05d65d20-2d63-460c-87256-de8DSh8il3217d2';

    public $message = 'Les données sont erroné.';

    public $messageOneSalesChannelForPartner = 'Veuillez choisir un seul canal de vente pour le partenaire.';
    public $messageMacroPartnerError = 'Vous ne pouvez pas définir un partenaire globale( le champs: est-ce globale) est lui associé un partenaire globale( Le champ Partenaire globale).';
    public $messageSameSalesChannelForMacroPartner = 'Veuillez choisir les mêmes canaux de ventes configurés pour vos partenaires rattachés. La bon choix est: {{ value }}';

    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}