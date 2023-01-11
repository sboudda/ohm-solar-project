<?php

namespace App\Validator\Constraints;

use App\Entity\ContractDraft;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class VerifiedContractValidator extends ConstraintValidator
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
        if (!$constraint instanceof VerifiedContract) {
            throw new UnexpectedTypeException($constraint, VerifiedContract::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $contract = $this->entityManager->getRepository(ContractDraft::class)
            ->findOneBy([
                'reference' => $value,
            ]);

        if (!$contract) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(VerifiedContract::INVALID_VALUE_ERROR)
                ->addViolation();
        }
    }
}
