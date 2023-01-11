<?php

namespace App\Validator\Constraints;

use App\DBAL\Types\ContractExternalState;
use App\DBAL\Types\ContractInternalStateType;
use App\Entity\ContractDraft;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContractFinalizedValidator extends ConstraintValidator
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
        if (!$constraint instanceof ContractFinalized) {
            throw new UnexpectedTypeException($constraint, ContractFinalized::class);
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

        if ($contract) {
            $finalizedContractStatus = array_merge(ContractExternalState::getFinalizedChoices(), [ContractInternalStateType::FINALIZED]);
            if ($contract->getInternalState() === ContractInternalStateType::ABANDONED_FOR_STAND_BY || !empty($contract->getSubstitutedBy())) {
                $this->context->buildViolation($constraint->messageAlreadyDuplicatedContract)
                    ->setCode(ContractFinalized::INVALID_CONTRACT_STATUS)
                    ->addViolation();
            } elseif (!in_array($contract->getStatus(), $finalizedContractStatus)) {
                $this->context->buildViolation($constraint->message)
                    ->setCode(ContractFinalized::INVALID_CONTRACT_STATUS)
                    ->addViolation();
            }
        } else {
            $this->context->buildViolation($constraint->messageInvalidContract)
                ->setCode(ContractFinalized::INVALID_CONTRACT)
                ->addViolation();
        }
    }
}
