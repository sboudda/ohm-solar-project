<?php

namespace App\Manager;

use App\Entity\PostalReferentiel;
use App\Http\Request\ELDElec\SearchDeliveryPoint;
use App\Utilities\JsonUtilities;
use App\Utilities\TraceJourneyHandler;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class DeliveryPointSearchManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TraceJourneyHandler
     */
    private $logger;

    /**
     * @var SearchDeliveryPoint
     */
    private $searchPDL;

    /**
     * @var ContainerBagInterface
     */
    private $containerBag;

    /**
     * @var SearchPceByAddress
     */
    private $searchPCE;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        SearchDeliveryPoint    $searchPDL
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = "";
        $this->containerBag = $containerBag;
        $this->searchPDL = $searchPDL;
        $this->searchPCE = "";
    }

    public function searchPdlByAddress($data)
    {
        $inseeCode = $this->guessInseeCode($data);
        if (!$inseeCode) {
            return null;
        }

        $addressObject = new stdClass();
        $addressObject->numeroEtNomVoie = $data['lineone'];
        $addressObject->codePostal = $data['zipcode'];
        $addressObject->codeInseeCommune = $inseeCode;
//        $adressObject->escalierEtEtageEtAppartement = '4 etg';
//        $adressObject->batiment = 'D42454';

        $searchCriteriaObject = new stdClass();
        $searchCriteriaObject->adresseInstallation = $addressObject;

        // si on ajoute le parametre nomClientFinalOuDenominationSociale sans précoion génère une regression
        // Verifier si l'action provient du web or Phone
//        if ($data->getJourney() === JourneyType::WEB_JOURNEY) {
        // TODO
        // ce paramètre n'est plus obligatoir sur enedis, si enedis joue avec se paramètre
        // donc il faut prévoir une solution ultras rapide pour contourner le problème s'il va se passer
        //Amani :le parametre nomClientFinalOuDenominationSociale est rendu obligatoire sur enedis,
        // donc on doit envoyer ce parametre dans les 2 parcours web et phone et pas seulement  web
        $searchOutsideTheScope = true;
        if (!empty($data['lastname'])) {
            $searchCriteriaObject->nomClientFinalOuDenominationSociale = $data['lastname'];
        } elseif (!empty($data['registrationnumberorserialnumber'])) {
            $searchCriteriaObject->matriculeOuNumeroSerie = $data['registrationnumberorserialnumber'];
        }
//        }

        $searchCriteriaObject->rechercheHorsPerimetre = $searchOutsideTheScope;

        $bodyObject = new stdClass();
        $bodyObject->criteres = $searchCriteriaObject;
        $bodyObject->loginUtilisateur = $this->containerBag->get('enedis_api.login_user'); //'homologation@ohm-energie.com';
        // Si l'action provient du web => ne retourne pas la liste des PDL
//        $this->searchPDL->setSkipPDLs($data->getJourney() === JourneyType::WEB_JOURNEY);
        $this->searchPDL->setSkipPDLs(true); // on retourne plus la liste des pdl dans le phone

        $result = $this->searchPDL->send($bodyObject);

        if (!empty($result)) {
            $jsonUtilities = new JsonUtilities();
            $jsonResponse = $jsonUtilities->ConvertObjectToJson($result);
     // comment for now until new logger set
           /* $this->logger->info(
                JourneyTracerContextType::ENEDIS_SEARCH_ADDRESS,
                'Recherche du PDL via adresse',
                null,
                $jsonResponse
            );*/

            return json_decode($jsonResponse);
        }

        return null;
    }

    private function guessInseeCode($data)
    {
        if (isset($data['inseecode']) && $data['inseecode'] ) {
            return $data['inseecode'];
        }

        /** @var PostalReferentiel|null $postalReferential */
        $postalReferential = $this->entityManager->getRepository(PostalReferentiel::class)
            ->findOneBy([
                'codePostal' => $data['zipcode'],
                'nomCommune' => $data['city'],
            ]);

        if (!$postalReferential) {
            $postalReferential = $this->entityManager->getRepository(PostalReferentiel::class)
                ->findOneBy([
                    'codePostal' =>  $data['zipcode'],
                    'libelleAcheminement' => $data['city']
                ]);
        }

        if ($postalReferential instanceof PostalReferentiel) {
            return $postalReferential->getCodeCommuneInsee();
        }

        return null;
    }


    private function guessAddressNumberOrStreet(string $lineOne, bool $isSearchingNumber = true): ?string
    {
        $billingAddressLineOneAsArray = explode(' ', trim($lineOne));

        if (is_array($billingAddressLineOneAsArray)
            && count($billingAddressLineOneAsArray) > 1) {
            if (preg_match('/^\d/', $billingAddressLineOneAsArray[0])) {
                if ($isSearchingNumber) {
                    return array_shift($billingAddressLineOneAsArray);
                } else {
                    return implode(' ', array_slice($billingAddressLineOneAsArray, 1));
                }
            } else {
                if (!$isSearchingNumber) {
                    return trim($lineOne);
                }
            }
        }

        return null;
    }
}
