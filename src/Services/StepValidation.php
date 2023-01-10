<?php

namespace App\Services;


use App\Entity\Prospect;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class StepValidation
{
   private $entityManager;

   private $session;

   private $router;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Session $session
     * @param RouterInterface $router
     */
    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session, RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->router = $router;
    }


    /**
     * @param $reference
     * @return void
     */
    public function validateReference($reference)
    {

        $prospect = $this->entityManager->getRepository(Prospect::class)->findOneBy(['reference' => $reference]);

        return $prospect ? $prospect : false;
    }


    public function redirectWithFlash()
    {
        $this->session->getFlashBag()->add(
            'notice',
            'Reference non trouvé veillez recommencer!');

        return new RedirectResponse($this->router->generate('step_one'));
    }

}