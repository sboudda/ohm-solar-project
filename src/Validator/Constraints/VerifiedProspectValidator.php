<?php

namespace App\Validator\Constraints;

use App\Entity\Prospect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class VerifiedProspectValidator extends ConstraintValidator
{
    private $propertyAccessor;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface    $entityManager,
        PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VerifiedProspect) {
            throw new UnexpectedTypeException($constraint, VerifiedProspect::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $prospect = $this->entityManager->getRepository(Prospect::class)
            ->findOneBy([
                'reference' => $value,
            ]);

        if (!$prospect) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(UtmSource::INVALID_VALUE_ERROR)
                ->addViolation();
        }
    }
}
