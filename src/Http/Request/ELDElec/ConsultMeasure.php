<?php

namespace App\Http\Request\ELDElec;

use App\Http\BaseHttpCalls;
use Exception;
use SoapClient;
use SoapParam;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class ConsultMeasure extends BaseHttpCalls
{
    // la période minimale pour accepter ou pas les relèves récupérer
    const MIN_MONTH_PERIOD = 9;
    const EXTRA_TEMPORAL_CLASS_CODE_HP_WE = 'HP_WE';
    const EXTRA_TEMPORAL_CLASS_CODE_HC_WE = 'HC_WE';
    const EXTRA_TEMPORAL_CLASS_CODE_WE_WE = 'WE_WE';
    const EXTRA_TEMPORAL_CLASS_CODES_WEEK_END = 'HC_HP_WE';

    const TEMPORAL_CLASS_CODE_HP = 'HP';
    const TEMPORAL_CLASS_CODE_HC = 'HC';
    const TEMPORAL_CLASS_CODE_BASE = 'BASE';
    const TEMPORAL_CLASS_CODE_HCB = 'HCB';
    const TEMPORAL_CLASS_CODE_HCH = 'HCH';
    const TEMPORAL_CLASS_CODE_HPB = 'HPB';
    const TEMPORAL_CLASS_CODE_HPH = 'HPH';

    const TEMPORAL_CLASS_CODE_HC_HP = 'HC_HP';
    const TEMPORAL_CLASS_CODE_HP_HC = 'HP_HC';
    const TEMPORAL_CLASS_CODE_HCB_HCH_HPB_HPH = 'HCB_HCH_HPB_HPH';
    const TEMPORAL_CLASS_CODE_HCB_HPB = 'HCB_HPB'; // => normally this format is non-existent
    const TEMPORAL_CLASS_CODE_HCH_HPH = 'HCH_HPH'; // => normally this format is non-existent

    const TEMPORAL_CLASS_CODE_MIXED_THREE = 'HC_HP_BASE';
    const TEMPORAL_CLASS_CODE_MIXED_THREE_HIGH_SEASON = 'BASE_HCH_HPH'; // => normally this format is non-existent
    const TEMPORAL_CLASS_CODE_MIXED_THREE_LOW_SEASON = 'BASE_HCB_HPB'; // => normally this format is non-existent

    const TEMPORAL_CLASS_CODE_MIXED_FIVE = 'BASE_HCB_HCH_HPB_HPH'; // 15230824880577 -- 14865122959521 --
    const TEMPORAL_CLASS_CODE_MIXED_FIVE_HIGH_SEASON = 'HC_HP_BASE_HCH_HPH'; // => normally this format is non-existent
    const TEMPORAL_CLASS_CODE_MIXED_FIVE_LOW_SEASON = 'HC_HP_BASE_HCB_HPB'; // => normally this format is non-existent

    const TEMPORAL_CLASS_CODE_MIXED_SIX = 'HC_HP_HCB_HCH_HPB_HPH';// 09370477534286 --
    const TEMPORAL_CLASS_CODE_MIXED_SEVEN = 'HC_HP_BASE_HCB_HCH_HPB_HPH'; // 14553256067144
    const TEMPORAL_CLASS_CODE_MIXED = 'MIXED';
    const TEMPORAL_CLASS_CODE_IRREGULAR = 'IRREGULAR';
    /**
     * @var stdClass
     */
    private $body;

    public function init()
    {
        $this->body = new stdClass();
        $this->body->loginDemandeur = $this->containerBag->get('enedis_api.login_user');
        $this->body->contratId = $this->containerBag->get('enedis_api.contract_id');
        $this->body->autorisationClient = true;

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

    public function send($params = [])
    {
        $certFile = $this->containerBag->get('ohm_certs.bundled_key');
        $wsdlFile = $this->containerBag->get('enedis_api.consult_measure.wsdl'); // this wsdl is modified, try to solve

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
            $requestData = new SoapParam($params, 'v11:consulterMesures');
        } else {
            $requestData = new SoapParam($this->body, 'v11:consulterMesures');
        }

        try {
            $startTime = microtime(true);
            $response = $soapClient->consulterMesures($requestData);
            $endTime = microtime(true);
            $this->logTheCallDuration(get_class($this), $startTime, $endTime);
        } catch (Exception $e) {
            return [
                'code' => $this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()),
                'message' => 'Erreur technique lors de la recherche point',
                'body' => [],
            ];
        }

        if ($this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()) === Response::HTTP_OK) {
            return [
                'code' => Response::HTTP_OK,
                'message' => 'Data ok',
                'body' => $this->xml2array($response),
            ];
        }

        return [
            'code' => $this->getStatusCodeFromXml($soapClient->__getLastResponseHeaders()),
            'message' => 'Erreur technique surla recherche point',
            'body' => [],
        ];
    }

    public function enhanceMeasure(array $seriesMesures): array
    {
        $dataSource = '';
        if (array_key_exists('seriesMesuresDateesGrilleTurpe', $seriesMesures)) {
            $dataSource = 'seriesMesuresDateesGrilleTurpe';
            unset($seriesMesures['seriesMesuresDateesGrilleFrn']);
        } elseif (array_key_exists('seriesMesuresDateesGrilleFrn', $seriesMesures)) {
            $dataSource = 'seriesMesuresDateesGrilleFrn';
            unset($seriesMesures['seriesMesuresDateesGrilleTurpe']);
        }

        if (empty($dataSource)) {
            return [];
        }

        $seriesMesures = $seriesMesures[$dataSource];

        if (empty($seriesMesures)) {
            return [];
        }

        if (!array_key_exists('serie', $seriesMesures)) {
            return [];
        }

        $seriesMesures = $this->prepareSeries($seriesMesures);

        return $this->prepareMesuresInsideSeries($seriesMesures);
    }

    private function prepareSeries(array $seriesMesures): array
    {
        foreach ($seriesMesures['serie'] as $key => $seriesMesure) {
            // c'est le cas ou il y a une seule série de mesures
            if (!is_numeric($key)) {
                $seriesMesures['serie'] = [$seriesMesures['serie']];
                break;
            }
        }

        return $seriesMesures;
    }

    private function prepareMesuresInsideSeries(array $seriesMesures)
    {
        foreach ($seriesMesures['serie'] as $key => $collectionMesure) {
            if (!array_key_exists('mesuresDatees', $collectionMesure)) {
                continue;
            }

            if (is_array($collectionMesure['mesuresDatees']) && array_key_exists('mesure', $collectionMesure['mesuresDatees'])) {
                foreach ($collectionMesure['mesuresDatees']['mesure'] as $index => $measuredValue) {
                    // c'est le cas ou il y a une seule mesure dans la série
                    if (!is_numeric($index)) {
                        // todo test avec une seul mesure dans la mesuresDatees
                        $seriesMesures['serie'][$key]['mesuresDatees']['mesure'] = [$collectionMesure['mesuresDatees']['mesure']];
                        break;
                    }
                }
            }
        }

        return $seriesMesures;
    }
}
