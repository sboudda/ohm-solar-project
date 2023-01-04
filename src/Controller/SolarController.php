<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Prospect;
use App\Entity\Toit;
use App\Form\data\StepFiveData;
use App\Form\data\StepOneData;
use App\Form\StepFiveFormType;
use App\Services\StepValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\data\StepTwoData;
use App\Form\data\StepThirdData;
use App\Form\StepOneFormType;
use App\Form\StepTwoFormType;

use App\Form\StepThreeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SolarController extends AbstractController
{
    private $stepvalidator;

    public function __construct(StepValidation $stepvalidator)
    {
        $this->stepvalidator = $stepvalidator;
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
    public function stepOne(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = [];
        $step = new StepOneData();
        $form = $this->createForm(StepOneFormType::class, $step);
        $form->handleRequest($request);
        $data['form'] = $form->createView();
        if ($form->isSubmitted() && $form->isValid()) {
            //save to DB
            $prospect = new Prospect();
            $address = new Address();
            $address->setFavorite(1);
            $client = new Client();
            $roof = new Toit();
            $prospect->setReference($prospect::generateProspectReference());
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
            $prospect = $this->stepvalidator->validateReference($reference);
            if(!$prospect)
            {
              return   $this->stepvalidator->redirectWithFlash();
            }

            $form = $this->createForm(StepTwoFormType::class, $step);
            $form->handleRequest($request);
            $data['form'] = $form->createView();
            $client = $prospect->getClient();
            $ClientObj = $entityManager->getRepository(Client::class)->getFavoriteAddress($client);
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
            $prospect = $this->stepvalidator->validateReference($reference);
            if(!$prospect)
            {
                return   $this->stepvalidator->redirectWithFlash();
            }

            $client = $prospect->getClient();
            $ClientObj = $entityManager->getRepository(Client::class)->getFavoriteAddress($client);
            $address = $ClientObj->getAddress()->first();

            $form = $this->createForm(StepThreeFormType::class, $step);
            $form->handleRequest($request);
            $data['form'] = $form->createView();
            if ($form->isSubmitted() && $form->isValid()) {
                //get the name of the button clicked

                // save the geocode and go to step 3
                $client = $prospect->getClient();
                $ClientObj = $entityManager->getRepository(Client::class)->getFavoriteAddress($client);
                $address = $ClientObj->getAddress()->first();
                $roof = $prospect->getToit();
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
            $prospect = $this->stepvalidator->validateReference($reference);
            if(!$prospect)
            {
                return   $this->stepvalidator->redirectWithFlash();
            }

            $form = $this->createForm(StepFiveFormType::class, $step);
            $form->handleRequest($request);
            $data['form'] = $form->createView();
            if ($form->isSubmitted() && $form->isValid()) {

                // save the geocode and go to step 6
               $toit = $prospect->getToit();
               $toit->setOrientation($step->getOrientation());
               $entityManager->persist($prospect);
               $entityManager->flush();


                return $this->redirectToRoute('step_six', ['reference' => $prospect->getReference()]);

            }

            return $this->render('step/stepFive.html.twig', $data);
        }
    }
}