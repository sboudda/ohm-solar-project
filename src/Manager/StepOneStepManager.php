<?php

namespace App\Manager;

use App\Entity\Prospect;
use App\Form\data\StepOneData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class StepOneStepManager extends StepManager
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, TokenStorageInterface $tokenStorage)
   {
       parent::__construct($em, $requestStack, $tokenStorage);
       $this->em = $em;
       $this->requestStack = $requestStack;
       $this->tokenStorage = $tokenStorage;
   }

    public function transformStep($step)
    {
        // TODO with the new feature of the interContractCode, this condition will not be good, we must add the test on this new property
        if ($this->request->get('reference')) {
            // edition d'une souscription
            /** @var Prospect $prospect */
            $prospect = $this->entityManager
                ->getRepository(Prospect::class)
                ->findOneBy([
                    'reference' => $this->request->get('reference')
                ]);

            $this->editProspectPortfolio($prospect, $step);
        } else {
            // creation d'une souscription
            $prospect = new Prospect();
            $prospect->preSetReference($step->getProspectReference())
                ->setCurrentStep(StepNumberType::GENERIC_INFORMATION_STEP)
                ->setEnergy($step->getContratEnergy());

            if (!empty($step->getIsSponsoredSubscription())) {
                $sponsorShipData = new SponsorShipData();
                $sponsorShipData->setSponsorLastname($step->getSponsorLastname())
                    ->setSponsorFirstName($step->getSponsorFirstName())
                    ->setSponsorEmail($step->getSponsorEmail())
                    ->setSponsorShipCode($step->getSponsorshipCode())
                    ->setVoucherCode($step->getVoucherCode())
                    ->setProspect($prospect)
                    ->setStatus(BuyapowaStatusType::PENDING);

                $this->entityManager->persist($sponsorShipData);
            }

            $user = $this->findBusinessAgent();

            switch ($step->getContratEnergy()) {
                case EnergyType::GAZ_ENERGY:
                case EnergyType::ELEC_ENERGY:
                    $contract = new ContractDraft($step->getContratEnergy());
                    $contract->setType($step->getContractType())
                        ->setOhmPartner($user->getProfile())
                        ->setBusinessAgent($user);
                    $prospect->addContractDraft($contract);
                    break;
                case EnergyType::DUAL_ENERGY:
                    $contractElec = new ContractDraft(EnergyType::ELEC_ENERGY);
                    $contractElec->setType($step->getContractType())
                        ->setProspect($prospect)
                        ->setOhmPartner($user->getProfile())
                        ->setBusinessAgent($user);
                    $prospect->addContractDraft($contractElec);
                    $contractGaz = new ContractDraft(EnergyType::GAZ_ENERGY);
                    $contractGaz->setType($step->getContractType())
                        ->setProspect($prospect)
                        ->setOhmPartner($user->getProfile())
                        ->setBusinessAgent($user);
                    $prospect->addContractDraft($contractGaz);
                    $this->entityManager->persist($contractElec);
                    $this->entityManager->persist($contractGaz);
                    break;
                default:
                    throw new ValidatorException('il faut fournir une énergie au contrat');
            }

            $contact = new Contact();
            $contact->setProspect($prospect)
                ->setPrincipal()
                ->setCivility($stepZeroCommon->getCivility())
                ->setFirstName($this->stringUtilities->sanitizeString($stepZeroCommon->getFirstName()))
                ->setDeliveryMode(DeliveryModeType::EMAIL)
                ->setLastName($this->stringUtilities->sanitizeString($stepZeroCommon->getLastName()))
                ->setMail($stepZeroCommon->getMail())
                ->setPhone($stepZeroCommon->getPhone())
                ->setPhoneValidationStatusFromApi($stepZeroCommon->getPhoneValidationStatusFromApi())
                ->setEmailValidationStatusFromApi($stepZeroCommon->getEmailValidationStatusFromApi());

            $this->entityManager->persist($contact);
            $this->entityManager->persist($prospect);
            $this->entityManager->flush();
        }
    }

    /**
     * @param StepOneData $step
     * @return StepZeroCommonData|mixed
     * @TODO try to use the property 'interContractCode' to find the appropriate contract
     */
    public function reverseTransformStep($step)
    {
        $prospect = $this->findCurrentProspect();
        if ($prospect) {

                $step->setAddress($prospect->getEnergy())->setProspectReference($prospect->getReference());
        }

        return $step;
    }


}