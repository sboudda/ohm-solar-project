<?php

namespace App\Utilities;

use App\Constant\Constant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JourneyUtilities
{
    /**
     * @var Request|null
     */
    private $request;

    /**
     * JourneyUtilities constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function guessJourney(): string
    {
        // TODO use the controller name from the request to detect journey
        if ($this->request) {
            // $controller = explode('::', $this->request->get('_controller'))[0];
            if ($this->request->get('journey') && Constant::isValueExist($this->request->get('journey'))) {
                return $this->request->get('journey');
            } elseif (strpos($this->request->getRequestUri(), '/phone') !== false) {
                $journey = Constant::PHONE_JOURNEY;
            } elseif (strpos($this->request->getRequestUri(), '/admin') !== false) {
                $journey = Constant::ADMINISTRATION;
            } elseif (strpos($this->request->getRequestUri(), '/api') !== false) {
                $journey = Constant::API;
            } elseif (strpos($this->request->getRequestUri(), '/ajax') !== false
                || strpos($this->request->getRequestUri(), '/ajax-anticipation') !== false) {
                $journey = Constant::AJAX_JOURNEY_CALLS;
            } elseif (strpos($this->request->getRequestUri(), '/tools') !== false) {
                $journey = Constant::JOURNEY_TOOLS;
            } elseif (strpos($this->request->getRequestUri(), '/_wdt/') !== false) {
                $journey = Constant::DEV_TOOLS;
            } elseif (strpos($this->request->getRequestUri(), '/assets/') !== false) {
                $journey = Constant::RESSOURCES_TOOLS;
            } else {
                $journey = Constant::WEB_JOURNEY;
            }
        } else {
            $journey = Constant::COMMAND_JOURNEY;
        }

        return $journey;
    }

    public function guessClientReference(?string $referenceClient)
    {
        if (!empty($referenceClient)) {
            return $referenceClient;
        }

        if ($this->request) {
            $reference =
                $this->request->get('reference')
                ?? $this->request->get('prospect_reference')
                ?? $this->request->get('prospectReference')
                ?? $this->request->getSession()->get('prospect_reference_in_current_journey')
                ?? 'UNKNOWN';

            return substr($reference, 0, 59);
        }

        return 'UNKNOWN';
    }
}
