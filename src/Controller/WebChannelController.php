<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Contact;
use App\Entity\Prospect;
use App\Entity\Roof;
use App\Form\data\StepFiveData;
use App\Form\data\StepOneData;
use App\Form\StepFiveFormType;
use App\Manager\DeliveryPointSearchManager;
use App\Manager\OhmApiManager;
use App\Manager\StepOneStepManager;
use App\Services\StepValidation;
use App\Form\data\StepTwoData;
use App\Form\data\StepThirdData;
use App\Form\StepOneFormType;
use App\Form\StepTwoFormType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\StepThreeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebChannelController extends AbstractController
{
    private $stepValidator;

    public function __construct(StepValidation $stepValidator)
    {
        $this->stepValidator = $stepValidator;
    }


    /**
     * @Route("/", name="app_solar")
     */
    public function index(): Response
    {
        return $this->render('step/generic.html.twig');
    }

    /**
     * @Route("/quote", name="step_one")
     */
    public function stepOne(Request $request, EntityManagerInterface $entityManager,SessionInterface $session, StepOneStepManager $stepManager): Response
    {
        $data = [];
        $step = new StepOneData();
        // on est en mode modif, car on a une reference client
        if ($prospect = $stepManager->findCurrentProspect()) {
            $step = $stepManager->reverseTransformStep($step);
        }
        $form = $this->createForm(StepOneFormType::class, $step);
        $form->handleRequest($request);
        $data['form'] = $form->createView();
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$step->getProspectReference()) {
                $prospect = new Prospect();
                $step->setProspectReference(Prospect::generateProspectReference());
            }
            $prospect = $stepManager->transformStep($step);

            //save to DB
            // in the future we shall move this dirty work to a manager


            $address = new Address();
            $address->setFavorite(1);
            $client = new Contact();
            $roof = new Roof();
            $prospect->setReference($prospect::generateProspectReference());
            //set the session
            $session->set('prospect_reference_in_current_journey',$prospect->getReference());
            $roof->setProspect($prospect);
            $client->addProspect($prospect);
            $address->setRawAddress($step->getAddress());
            $client->addAddress($address);
            $entityManager->persist($prospect);
            $entityManager->persist($roof);
            $entityManager->persist($address);
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('step_two', ['reference' => $prospect->getReference()]);

        }


        return $this->render('step/stepOne.html.twig', $data);
    }

    /**
     * We make sure the reference exists and displays the google map with the address saved.
     * @Route("/steptwo/{reference}", name="step_two")
     * @return Response
     */
    public function stepTwo($reference, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = [];
        $step = new StepTwoData();
        if ($reference) {
            /**
             * Prospect $prospect
             */
            $prospect = $this->stepValidator->validateReference($reference);
            if(!$prospect)
            {
              return   $this->stepValidator->redirectWithFlash();
            }

            $form = $this->createForm(StepTwoFormType::class, $step);
            $form->handleRequest($request);
            $data['form'] = $form->createView();
            $client = $prospect->getClient();
            $ClientObj = $entityManager->getRepository(Contact::class)->getFavoriteAddress($client);
            $address = $ClientObj->getAddress()->first();

            if ($form->isSubmitted() && $form->isValid()) {
                //get the name of the button clicked

                if ($form->getClickedButton() && $form->getClickedButton()->getName() == 'next') {
                    // save the geocode and go to step 3
                    $address->setGeoCode([$step->getGeoCodeLat(), $step->getGeoCodeLng()]);
                    $entityManager->persist($address);
                    $entityManager->flush();

                } else {
                    //clicked on notfound btn
                    //go to step 4
                    return $this->redirectToRoute('step_four', ['reference' => $prospect->getReference()]);
                }

                return $this->redirectToRoute('step_three', ['reference' => $prospect->getReference()]);

            }

            //get the first address
            $data["hidden_address"] = $address->getRawAddress();
            return $this->render('step/stepTwo.html.twig', $data);

        }



    }
    /**
     * We make sure the reference exists and displays the google map with the geocode saved and
     * the user will draw his roof by moving the 4 boundaries saved.
     * We need to save the four boundaries to calculate the  area
     * @Route("/stepthree/{reference}", name="step_three")
     * @return Response
     */
    public function stepThree($reference, Request $request, EntityManagerInterface $entityManager )
    {
        $data = [];
        $step = new StepThirdData();

        if ($reference) {
            /**
             * Prospect $prospect
             */
            $prospect = $this->stepValidator->validateReference($reference);
            if(!$prospect)
            {
                return   $this->stepValidator->redirectWithFlash();
            }

            $client = $prospect->getClient();
            $ClientObj = $entityManager->getRepository(Contact::class)->getFavoriteAddress($client);
            $address = $ClientObj->getAddress()->first();

            $form = $this->createForm(StepThreeFormType::class, $step);
            $form->handleRequest($request);
            $data['form'] = $form->createView();
            if ($form->isSubmitted() && $form->isValid()) {
                //get the name of the button clicked

                // save the geocode and go to step 3
                $client = $prospect->getClient();
                $ClientObj = $entityManager->getRepository(Contact::class)->getFavoriteAddress($client);
                $address = $ClientObj->getAddress()->first();
                $roof = $prospect->getRoof();
                $roof->setArea($step->getArea());
                $address->setPoints([$step->getBorderA(), $step->getBorderB(), $step->getBorderC(), $step->getBorderD()]);
                $entityManager->persist($address);
                $entityManager->flush();

                return $this->redirectToRoute('step_five', ['reference' => $prospect->getReference()]);

            }

            //get the first address
            $data["hidden_address"] = $address->getGeoCode();
            return $this->render('step/stepThree.html.twig', $data);
        }
    }

    /**
     * We make sure the reference exists and displays the compass
     * @Route("/stepfive/{reference}", name="step_five")
     * @return Response
     */
    public function stepFive($reference, Request $request, EntityManagerInterface $entityManager )
    {
        $data = [];
        $step = new StepFiveData();

        if ($reference) {
            /**
             * Prospect $prospect
             */
            $prospect = $this->stepValidator->validateReference($reference);
            if(!$prospect)
            {
                return   $this->stepValidator->redirectWithFlash();
            }

            $form = $this->createForm(StepFiveFormType::class, $step);
            $form->handleRequest($request);
            $data['form'] = $form->createView();
            if ($form->isSubmitted() && $form->isValid()) {

                // save the geocode and go to step 6
               $toit = $prospect->getRoof();
               $toit->setOrientation($step->getOrientation());
               $entityManager->persist($prospect);
               $entityManager->flush();

                return $this->redirectToRoute('step_six', ['reference' => $prospect->getReference()]);

            }

            return $this->render('step/stepFive.html.twig', $data);
        }
    }

    /**
     * We make sure the reference exists and displays the compass
     * @Route("/pointpdl", name="pointpdl")
     * @return Response
     */
    public function searchPdlByAddress(DeliveryPointSearchManager $deliveryPointSearchManager)
    {

        $data = [];
        $data['zipcode'] = "84000";
        $data['city'] = "AVIGNON";
        $data['lineone'] = "8 bd MARCEL COMBE";
        // constraint : to search outside bounds we need to fill the lastname
        $data['lastname'] = "AIDOUDI"; // AIDOUDI
        $data['registrationnumberorserialnumber'] = ""; //registrationNumberOrSerialNumber
        $result =  $deliveryPointSearchManager->searchPdlByAddress($data);
        dump($result);

        return new Response('ok');
    }

    /**
     * We make sure the reference exists and displays the compass
     * @Route("/token_test", name="token")
     * @return Response
     */
    public function getToken(OhmApiManager $apiManager)
    {


        $token =  $apiManager->getToken();
        //get the estimation for the client given the pdl

        return new Response('ok');


    }



}