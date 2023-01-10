<?php

namespace App\Http\Request\ELDElec;

use App\Constant\Constant;
use App\DBAL\Types\DistributionCompanyType;
use App\DBAL\Types\JourneyTracerContextType;
use App\Http\BaseHttpCalls;
use Exception;
use SoapClient;
use SoapParam;
use Symfony\Component\HttpFoundation\Response;

class SearchDeliveryPoint extends BaseHttpCalls
{
    private $skipPDLs;

    public function init()
    {
        // nothing special to do here

        return $this;
    }

    public function send($params = [])
    {
        $certFile = $this->containerBag->get('ohm_certs.bundled_key');
        $wsdlFile = $this->containerBag->get('enedis_api.research_point.wsdl');

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

        try {
            // Get Address
            $startTime = microtime(true);
            $request_data = new SoapParam($params, 'v2:rechercherPoint');
            $response = $soapClient->rechercherPoint($request_data);
            $endTime = microtime(true);
            $this->logTheCallDuration(get_class($this), $startTime, $endTime);

            if ($this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()) === Response::HTTP_OK) {
                $allPointsData = json_decode(json_encode($response), true);
                $allPointsData = $this->getValueFromArray($allPointsData, ['points', 'point'], []);
                if (!is_array($allPointsData)) {
                    die("Error no points data");
                 /*   $this->logger->emergency(
                        JourneyTracerContextType::ENEDIS_SEARCH_ADDRESS,
                        is_string($allPointsData) ? $allPointsData : serialize($allPointsData),
                        'undefined_reference',
                        serialize($params),
                        'undefined_reference'
                    );*/

                    $allPointsData = [];
                }

                $reformedDataForResponse = $this->formatDataForResponse(
                    array_key_exists('adresseInstallationNormalisee', $allPointsData)
                        ? [$allPointsData]
                        : $allPointsData
                ); // TODO verif if it's an array

                if ($this->skipPDLs && count($reformedDataForResponse) > 1) {
                    return [
                        'code' => $this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()),
                        'message' => 'Detection impossible du point',
                        'body' => [],
                    ];
                }

                return [
                    'code' => $this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()),
                    'body' => $reformedDataForResponse,
                ];
            }
        } catch (Exception $exception) {
            return [
                'code' => $exception->getCode() == 0
                    ? Response::HTTP_NOT_ACCEPTABLE
                    : Response::HTTP_NOT_FOUND,
                'message' => $exception->getMessage(),
                'internalCode' => $exception->getCode(),
                'body' => [],
            ];
        }
    }

    public function getStatusCodeFromXml($headers)
    {
        preg_match("/HTTP\/\d\.\d\s*\K[\d]+/", $headers, $matches);
        return is_array($matches) && count($matches) > 0 ? (int)$matches[0] : Response::HTTP_VERSION_NOT_SUPPORTED; // la version de retour xml n’est pas supporté
    }

    /**
     * @param mixed $skipPDLs
     */
    public function setSkipPDLs($skipPDLs): void
    {
        $this->skipPDLs = $skipPDLs;
    }

    private function formatDataForResponse(array $allPointsData)
    {
        $result = [];
        foreach ($allPointsData as $pointData) {

            $cityAndZipCode = $this->splitDigitFromString($this->getValueFromArray($pointData, ['adresseInstallationNormalisee', 'ligne6']));
            $lineTwo = $this->getValueFromArray($pointData, ['adresseInstallationNormalisee', 'ligne2']);
            $lineThree = $this->getValueFromArray($pointData, ['adresseInstallationNormalisee', 'ligne3']);
            $deliveryPoint = [
                'counterNumber' => $this->getValueFromArray($pointData, ['numeroSerie']),
                'isRecognizedAuto' => true,
                'pdlPce' => $this->getValueFromArray($pointData, ['id']),
                'localDistributionCompany' => Constant::ELEC_COMPANY_ENEDIS,
                'counterType' => $this->getValueFromArray($pointData, ['typeComptage', 'code']),
                'contractualState' => $this->getValueFromArray($pointData, [ 'etatContractuel', 'code']),
                'consumptionAddress' => $this->getValueFromArray($pointData, ['adresseInstallationNormalisee', 'ligne4'])
                    . ' -' . $cityAndZipCode['digit'] . ' -' . $cityAndZipCode['string'],
                'address' => [
                    'lineOne' => $this->getValueFromArray($pointData, ['adresseInstallationNormalisee', 'ligne4']),
                    'lineTwo' => $lineTwo,
                    'lineThree' => $lineThree,
                    'zipCode' => $cityAndZipCode['digit'],
                    'city' => $cityAndZipCode['string'],
                    'country' => "FR",
                    'lastContractedUser' => $this->getValueFromArray($pointData, ['nomClientFinalOuDenominationSociale']),
                    'complement' => $lineTwo . ' ' . $lineThree,
                    'fullName' => $this->getValueFromArray($pointData, ['nomClientFinalOuDenominationSociale']),
                ],
            ];

            array_push($result, $deliveryPoint);
        }

        return $result;
    }

    public function splitDigitFromString(string $word)
    {
        $result = [
            'digit' => null,
            'string' => null,
        ];

        preg_match("/^(\d{5})(.*)$/", $word, $elems);
        array_shift($elems);
        foreach ($elems as $elem) {
            $elem = trim($elem);
            if (is_numeric($elem)) {
                $result['digit'] = $elem;
            } elseif (is_string($elem)) {
                $result['string'] = $elem;
            }
        }

        return $result;
    }


}