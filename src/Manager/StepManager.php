<?php

namespace App\Manager;

use App\Entity\Prospect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class StepManager
{
    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param mixed $step
     * @return mixed
     */
    abstract public function transformStep($step);

    /**
     * @return Prospect|null
     */
    public function findCurrentProspect()
    {
        if ($this->request) {
            $reference = $this->request->get('reference') ?? $this->request->get('clientReference');
            if ($reference) {
                return $this->entityManager
                    ->getRepository(Prospect::class)
                    ->findOneBy([
                        'reference' => $reference
                    ]);
            }
        }

        return null;
    }

    /**
     * @param mixed $step
     * @return mixed
     */
    abstract public function reverseTransformStep($step);


}