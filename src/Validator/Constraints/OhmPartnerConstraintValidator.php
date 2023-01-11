<?php

namespace App\Validator\Constraints;

use App\DBAL\Types\ChannelJourneyType;
use App\Entity\OhmPartner as OhmPartnerDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OhmPartnerConstraintValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param OhmPartnerDto $object
     * @param Constraint $constraint
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof OhmPartnerConstraint) {
            throw new UnexpectedTypeException($constraint, OhmPartnerConstraint::class);
        }

        if (null === $object) {
            return;
        }

        $this->validateAll($object, $constraint);

    }

    private function validateAll(OhmPartnerDto $object, OhmPartnerConstraint $constraint)
    {
        $this->validateSalesChannelSection($object, $constraint);
        $this->validateMacroPartnerDefinition($object, $constraint);

    }

    private function validateSalesChannelSection(OhmPartnerDto $object, OhmPartnerConstraint $constraint)
    {
        if (!$object->getIsMacroPartner()) {
            if (count($object->getSalesChannel()) > 1) {
                $this->addFormIntegrityViolation(
                    $constraint->messageOneSalesChannelForPartner,
                    'salesChannel',
                    '',
                    OhmPartnerConstraint::INVALID_VALUE_ERROR,
                    ''
                );
            }
        } else {
            $requiredSalesChannel = [];
            foreach ($object->getOhmPartners() as $partner) {
                $requiredSalesChannel = array_merge($requiredSalesChannel, $partner->getSalesChannel() ?? []);
            }

            $requiredSalesChannel = array_unique($requiredSalesChannel);
            $macroPartnerSalesChannel = $object->getSalesChannel();
            if ($macroPartnerSalesChannel) {
                sort($macroPartnerSalesChannel);
            }

            if ($requiredSalesChannel) {
                sort($requiredSalesChannel);
            }

            if (!empty($requiredSalesChannel) && $macroPartnerSalesChannel !== $requiredSalesChannel) {
                $humanizedSalesChannel = [];
                foreach ($requiredSalesChannel as $salesChannel) {
                    array_push($humanizedSalesChannel, ChannelJourneyType::getReadableValue($salesChannel));
                }
                $this->addFormIntegrityViolation(
                    $constraint->messageSameSalesChannelForMacroPartner,
                    'salesChannel',
                    implode(", ", $humanizedSalesChannel),
                    OhmPartnerConstraint::INVALID_VALUE_ERROR,
                    ''
                );
            }
        }
    }

    private function addFormIntegrityViolation($message, $errorPath, $messageParameter, $errorCode, $violationCause)
    {
        $this->context->buildViolation($message)
            ->atPath($errorPath)
            ->setParameter('{{ value }}', $messageParameter)
            // ->setInvalidValue($invalidValue)
            ->setCode($errorCode)
            ->setCause($violationCause)
            ->addViolation();
    }

    private function validateMacroPartnerDefinition(OhmPartnerDto $object, OhmPartnerConstraint $constraint)
    {
        if ($object->getIsMacroPartner() && !empty($object->getMacroOhmPartner())) {
            $this->addFormIntegrityViolation(
                $constraint->messageMacroPartnerError,
                'macroOhmPartner',
                '',
                OhmPartnerConstraint::INVALID_VALUE_ERROR,
                ''
            );
        }
    }
}
