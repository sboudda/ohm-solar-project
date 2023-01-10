<?php

namespace App\Http\Request\ELDElec;

use App\Http\BaseHttpCalls;
use Exception;
use SoapClient;
use SoapParam;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class ConsultDeliveryPoint extends BaseHttpCalls
{
    const PREFIX_ENEDIS_HC_TIME_SLOT = 'HC (';
    const SUFFIX_ENEDIS_HC_TIME_SLOT = ')';
    /**
     * @var stdClass
     */
    private $body;
    private $isWithSupplierCalendar = false;
    private $isWithExtraSensibleResult = false;

    public function init()
    {
        $this->body = new stdClass();
        $this->body->loginUtilisateur = $this->containerBag->get('enedis_api.login_user');
        $this->body->autorisationClient = true;
        $this->body->donneesDemandees = new stdClass();
        $this->body->donneesDemandees->donneesGeneralesPoint = true;
        $this->body->donneesDemandees->modificationsContractuellesEnCours = false;
        $this->body->donneesDemandees->interventionsEnCours = false;
        $this->body->donneesDemandees->historiqueAffaires = false;
        $this->body->donneesDemandees->elementsProchainesMesures = false;
        $this->body->donneesDemandees->derniersIndex = true;

        return $this;
    }

    /**
     * @param string $pointId
     * @return $this
     */
    public function withPointId(string $pointId): self
    {
        $this->body->pointId = $pointId;

        return $this;
    }

    /**
     * @param bool $withOngoingInterventions
     * @return $this
     */
    public function withOngoingInterventions($withOngoingInterventions = false): self
    {
        $this->body->donneesDemandees->interventionsEnCours = $withOngoingInterventions;

        return $this;
    }

    /**
     * @param string $pointId
     * @return $this
     */
    public function withBusinessHistory(bool $withBusinessHistory = false): self
    {
        $this->body->donneesDemandees->historiqueAffaires = $withBusinessHistory;

        return $this;
    }

    public function send($params = [])
    {
        $certFile = $this->containerBag->get('ohm_certs.bundled_key');
        $wsdlFile = $this->containerBag->get('enedis_api.consult_delivery_point.wsdl');

        /** @var SoapClient $soapClient */
        $soapClient = new SoapClient($wsdlFile, [
                'keep_alive' => true,
                'trace' => 1,
                'exceptions' => true,
                'local_cert' => $certFile,
                'style' => SOAP_RPC,
                'use' => SOAP_ENCODED,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]
        );

        if ($params) {
            $request = new SoapParam($params, 'v4:consulterDonneesPoint');
        } else {
            $request = new SoapParam($this->body, 'v4:consulterDonneesPoint');
        }

        try {
            $startTime = microtime(true);
            $response = $soapClient->consulterDonneesPoint($request);
            $endTime = microtime(true);
            $this->logTheCallDuration(get_class($this), $startTime, $endTime);

            if ($this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()) === Response::HTTP_OK) {
                $data = $this->xml2array($response);
                return [
                    'code' => $this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()),
                    'body' => $this->getAddressAndExtraData($data),
                ];
            } else {
                return [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Erreur technique',
                    'internalCode' => $this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()),
                    'body' => [],
                ];
            }
        } catch (Exception $exception) {
            return [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => $exception->getMessage(),
                'internalCode' => $exception->getCode(),
                'body' => [],
            ];
        }
    }

    public function getAddressAndExtraData($data)
    {
        // Valider que toutes les clefs sont existante dans le payload
        $futureHcTimeSlots = null;
        $businessHistory = null;
        $onGoingInterventions = null;
        $actualHcTimeSlots = null;
        $counterType = null;
        $subscribedPower = null;
        $contractualState = null;
        $openingLevel = null;
        $segment = null;
        $line1 = null;
        $zipCode = null;
        $city = null;
        $inseeCode = null;
        $nbCadrans = null;
        $feedingSituation = null;
        $dateDerniereModificationFormuleTarifaireAcheminement = null;
        $formuleTarifaireAcheminement = null;
        $lastContractedUser = null;
        $supplierTemporalClass = [];

        if (array_key_exists('situationComptage', $data['point'])) {
            if (is_array($data['point']['situationComptage'])) {
                if (array_key_exists('futuresPlagesHeuresCreuses', $data['point']['situationComptage'])) {
                    if (is_array($data['point']['situationComptage']['futuresPlagesHeuresCreuses'])) {
                        if (array_key_exists('libelle', $data['point']['situationComptage']['futuresPlagesHeuresCreuses'])) {
                            $futureHcTimeSlots = str_replace(self::PREFIX_ENEDIS_HC_TIME_SLOT, '', $data['point']['situationComptage']['futuresPlagesHeuresCreuses']['libelle']);
                            $futureHcTimeSlots = str_replace(self::SUFFIX_ENEDIS_HC_TIME_SLOT, '', $futureHcTimeSlots);
                        }
                    }
                }

                if (array_key_exists('dispositifComptage', $data['point']['situationComptage'])) {
                    if (is_array($data['point']['situationComptage']['dispositifComptage'])) {

                        if (array_key_exists('relais', $data['point']['situationComptage']['dispositifComptage'])) {
                            if (is_array($data['point']['situationComptage']['dispositifComptage']['relais'])) {
                                if (array_key_exists('plageHeuresCreuses', $data['point']['situationComptage']['dispositifComptage']['relais'])) {
                                    $actualHcTimeSlots = str_replace(
                                        self::PREFIX_ENEDIS_HC_TIME_SLOT, '',
                                        $data['point']['situationComptage']['dispositifComptage']['relais']['plageHeuresCreuses']
                                    );
                                    $actualHcTimeSlots = str_replace(self::SUFFIX_ENEDIS_HC_TIME_SLOT, '', $actualHcTimeSlots);
                                }
                            }
                        }

                        if (array_key_exists('typeComptage', $data['point']['situationComptage']['dispositifComptage'])) {
                            $counterType = strtoupper($data['point']['situationComptage']['dispositifComptage']['typeComptage']['code']);
                        }

                        if (array_key_exists('compteurs', $data['point']['situationComptage']['dispositifComptage'])) { // nbCadrans
                            if (is_array($data['point']['situationComptage']['dispositifComptage']['compteurs'])) {
                                if (array_key_exists('compteur', $data['point']['situationComptage']['dispositifComptage']['compteurs'])) {
                                    if (is_array($data['point']['situationComptage']['dispositifComptage']['compteurs']['compteur'])) {
                                        if (array_key_exists('modeleCompteur', $data['point']['situationComptage']['dispositifComptage']['compteurs']['compteur'])) {
                                            if (is_array($data['point']['situationComptage']['dispositifComptage']['compteurs']['compteur']['modeleCompteur'])) {
                                                if (array_key_exists('nbCadrans', $data['point']['situationComptage']['dispositifComptage']['compteurs']['compteur']['modeleCompteur'])) {
                                                    $nbCadrans = $data['point']['situationComptage']['dispositifComptage']['compteurs']['compteur']['modeleCompteur']['nbCadrans'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (array_key_exists('situationContractuelle', $data['point'])) {
            if (is_array($data['point']['situationContractuelle'])) {
                if (array_key_exists('structureTarifaire', $data['point']['situationContractuelle'])) {
                    if (is_array($data['point']['situationContractuelle']['structureTarifaire'])) {
                        if (array_key_exists('puissanceSouscriteMax', $data['point']['situationContractuelle']['structureTarifaire'])) {
                            if (is_array($data['point']['situationContractuelle']['structureTarifaire'])) {
                                if (array_key_exists('puissanceSouscriteMax', $data['point']['situationContractuelle']['structureTarifaire'])) {
                                    $subscribedPower = $data['point']['situationContractuelle']['structureTarifaire']['puissanceSouscriteMax']['valeur'];
                                }
                            }
                        }
                        if (array_key_exists('formuleTarifaireAcheminement', $data['point']['situationContractuelle']['structureTarifaire'])) {
                            $formuleTarifaireAcheminement = $data['point']['situationContractuelle']['structureTarifaire']['formuleTarifaireAcheminement']['code'];
                        }
                    }
                }

                if (array_key_exists('clientFinal', $data['point']['situationContractuelle'])) {
                    if (is_array($data['point']['situationContractuelle']['clientFinal'])) {
                        if (array_key_exists('personnePhysique', $data['point']['situationContractuelle']['clientFinal'])) {
                            if (is_array($data['point']['situationContractuelle']['clientFinal']['personnePhysique'])) {
                                if (array_key_exists('civilite', $data['point']['situationContractuelle']['clientFinal']['personnePhysique'])) {
                                    $lastContractedUser = $data['point']['situationContractuelle']['clientFinal']['personnePhysique']['civilite'] . ' ' .
                                        $data['point']['situationContractuelle']['clientFinal']['personnePhysique']['nom'] . ' ' .
                                        $data['point']['situationContractuelle']['clientFinal']['personnePhysique']['prenom'];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (array_key_exists('donneesGenerales', $data['point'])) {
            if (is_array($data['point']['donneesGenerales'])) {
                if (array_key_exists('etatContractuel', $data['point']['donneesGenerales'])) {
                    if (is_array($data['point']['donneesGenerales']['etatContractuel'])) {
                        $contractualState = strtoupper($data['point']['donneesGenerales']['etatContractuel']['code']);
                    }
                }

                if (array_key_exists('niveauOuvertureServices', $data['point']['donneesGenerales'])) {
                    $openingLevel = $data['point']['donneesGenerales']['niveauOuvertureServices'];
                }

                if (array_key_exists('segment', $data['point']['donneesGenerales'])) {
                    $segment = $data['point']['donneesGenerales']['segment']['libelle'];
                }

                if (array_key_exists('dateDerniereModificationFormuleTarifaireAcheminement', $data['point']['donneesGenerales'])) {
                    $dateDerniereModificationFormuleTarifaireAcheminement = $data['point']['donneesGenerales']['dateDerniereModificationFormuleTarifaireAcheminement'];
                }

                if (array_key_exists('adresseInstallation', $data['point']['donneesGenerales'])) {
                    if (is_array($data['point']['donneesGenerales']['adresseInstallation'])) {
                        if (array_key_exists('numeroEtNomVoie', $data['point']['donneesGenerales']['adresseInstallation'])) {
                            $line1 = $data['point']['donneesGenerales']['adresseInstallation']['numeroEtNomVoie'];
                        }

                        if (array_key_exists('codePostal', $data['point']['donneesGenerales']['adresseInstallation'])) {
                            $zipCode = $data['point']['donneesGenerales']['adresseInstallation']['codePostal'];
                        }

                        if (array_key_exists('commune', $data['point']['donneesGenerales']['adresseInstallation'])) {
                            if (is_array($data['point']['donneesGenerales']['adresseInstallation']['commune'])) {
                                if (array_key_exists('libelle', $data['point']['donneesGenerales']['adresseInstallation']['commune'])) {
                                    $city = $data['point']['donneesGenerales']['adresseInstallation']['commune']['libelle'];
                                }

                                if (array_key_exists('code', $data['point']['donneesGenerales']['adresseInstallation']['commune'])) {
                                    $inseeCode = $data['point']['donneesGenerales']['adresseInstallation']['commune']['code'];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (array_key_exists('situationAlimentation', $data['point'])) {
            if (is_array($data['point']['situationAlimentation'])) {
                if (array_key_exists('etatAlimentation', $data['point']['situationAlimentation'])) {
                    if (is_array($data['point']['situationAlimentation']['etatAlimentation'])) {
                        if (array_key_exists('code', $data['point']['situationAlimentation']['etatAlimentation'])) {
                            $feedingSituation = $data['point']['situationAlimentation']['etatAlimentation']['code'];
                        }
                    }
                }
            }
        }

        if ($this->isWithSupplierCalendar) {
            if (array_key_exists('derniersIndexReleves', $data['point']) && $this->isWithSupplierCalendar) {
                if (is_array($data['point']['derniersIndexReleves'])) {
                    if (array_key_exists('grilleTurpe', $data['point']['derniersIndexReleves'])) {
                        if (is_array($data['point']['derniersIndexReleves']['grilleTurpe'])) {
                            if (array_key_exists('classesTemporelles', $data['point']['derniersIndexReleves']['grilleTurpe'])) {
                                if (is_array($data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles'])) {
                                    if (array_key_exists('classeTemporelle', $data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles'])) {
                                        foreach ($data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles']['classeTemporelle'] as $key => $temporalClass) {
                                            if (is_string($key)) {
                                                if (array_key_exists('libelle', $data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles']['classeTemporelle'])
                                                    && array_key_exists('code', $data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles']['classeTemporelle'])) {
                                                    array_push($supplierTemporalClass, [
                                                        "libelle" => $data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles']['classeTemporelle']['libelle'],
                                                        "code" => $data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles']['classeTemporelle']['code'],
                                                        'minValue' => $data['point']['derniersIndexReleves']['grilleTurpe']['classesTemporelles']['classeTemporelle']['index']['valeur'],
                                                    ]);
                                                    break;
                                                }
                                            } elseif (is_integer($key)) {
                                                array_push($supplierTemporalClass, [
                                                    "libelle" => $temporalClass['libelle'],
                                                    "code" => $temporalClass['code'],
                                                    'minValue' => $temporalClass['index']['valeur'],
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (array_key_exists('grilleFrn', $data['point']['derniersIndexReleves'])) {
                        if (is_array($data['point']['derniersIndexReleves']['grilleFrn'])) {
                            if (array_key_exists('classesTemporelles', $data['point']['derniersIndexReleves']['grilleFrn'])) {
                                if (is_array($data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles'])) {
                                    if (array_key_exists('classeTemporelle', $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles'])) {
                                        if (array_key_exists('classeTemporelle', $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles'])) {

                                            foreach ($data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles']['classeTemporelle'] as $key => $temporalClass) {
                                                if (is_string($key)) {
                                                    if (array_key_exists('libelle', $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles']['classeTemporelle'])
                                                        && array_key_exists('code', $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles']['classeTemporelle'])) {
                                                        array_push($supplierTemporalClass, [
                                                            "libelle" => $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles']['classeTemporelle']['libelle'],
                                                            "code" => $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles']['classeTemporelle']['code'],
                                                            'minValue' => $data['point']['derniersIndexReleves']['grilleFrn']['classesTemporelles']['classeTemporelle']['index']['valeur'],
                                                        ]);
                                                        break;
                                                    }
                                                } elseif (is_integer($key)) {
                                                    array_push($supplierTemporalClass, [
                                                        "libelle" => $temporalClass['libelle'],
                                                        "code" => $temporalClass['code'],
                                                        'minValue' => $temporalClass['index']['valeur'],
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($this->getIsWithExtraSensibleResult()) {
            if (array_key_exists('historiqueAffaires', $data['point'])) {
                if (is_array($data['point']['historiqueAffaires'])) {
                    if (array_key_exists('affaire', $data['point']['historiqueAffaires'])) {
                        if (is_array($data['point']['historiqueAffaires']['affaire'])) {
                            $businessHistory = $data['point']['historiqueAffaires']['affaire'];
                        }
                    }
                }
            }

            if (array_key_exists('interventionsEnCours', $data['point'])) {
                if (is_array($data['point']['interventionsEnCours'])) {
                    if (array_key_exists('interventionEnCours', $data['point']['interventionsEnCours'])) {
                        if (is_array($data['point']['interventionsEnCours']['interventionEnCours'])) {
                            $onGoingInterventions = $data['point']['interventionsEnCours']['interventionEnCours'];
                        }
                    }
                }
            }
        }

        return [
            'line1' => $line1,
            'zipCode' => $zipCode,
            'city' => $city,
            'inseeCode' => $inseeCode,

            'subscribedPower' => $subscribedPower,
            'elecSegment' => $segment,
            'counterType' => $counterType,
            'openingLevel' => $openingLevel,
            'contractualState' => $contractualState,
            'actualHcTimeSlots' => $actualHcTimeSlots,
            'futureHcTimeSlots' => $futureHcTimeSlots,
            'nbCadrans' => $nbCadrans,
            'feedingSituation' => $feedingSituation,
            'formuleTarifaireAcheminement' => $formuleTarifaireAcheminement,
            'dateDerniereModificationFormuleTarifaireAcheminement' => $dateDerniereModificationFormuleTarifaireAcheminement,
            'supplierTemporalClass' => $supplierTemporalClass,
            'lastContractedUser' => $lastContractedUser,

            'businessHistory' => $businessHistory,
            'onGoingInterventions' => $onGoingInterventions,
        ];
    }

    public function withSupplierCalendar()
    {
        $this->isWithSupplierCalendar = true;

        return $this;
    }

    public function withoutSupplierCalendar()
    {
        $this->isWithSupplierCalendar = false;

        return $this;
    }

    public function withExtraSensibleResult()
    {
        $this->isWithExtraSensibleResult = true;

        return $this;
    }

    public function withoutExtraSensibleResult()
    {
        $this->isWithExtraSensibleResult = false;

        return $this;
    }

    public function getIsWithExtraSensibleResult()
    {
        return $this->isWithExtraSensibleResult;
    }
}
