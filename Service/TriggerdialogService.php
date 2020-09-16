<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Service;

if (!class_exists('Firebase\JWT\JWT', false)) {
    require_once __DIR__ . '/../Library/vendor/autoload.php';
}

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use MauticPlugin\MauticTriggerdialogBundle\Exception\RequestException;
use MauticPlugin\MauticTriggerdialogBundle\Utility\SsoUtility;

class TriggerdialogService
{
    const AUDIENCE = 'https://dm-uat.deutschepost.de/gateway/';

    const TEST_AUDIENCE = 'https://dm-uat.deutschepost.de/gateway/';

    /**
     * @var self
     */
    protected static $_instance;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $masId;

    /**
     * @var string
     */
    protected $masClientId;

    /**
     * @var string
     */
    protected $authenticationSecret;

    /**
     * @var array
     */
    protected $config = [
        'http_errors' => false,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ];

    private $masSecret;

    /**
     * TriggerdialogService constructor.
     *
     * @param int $masId
     * @param string $masClientId
     * @param $authenticationSecret
     */
    protected function __construct($masId, $masClientId, $authenticationSecret, $masSecret)
    {
        $audience = MAUTIC_ENV === 'prod' ? self::AUDIENCE : self::TEST_AUDIENCE;
        $this->config += ['base_uri' => $audience];
        $this->client = new Client($this->config);
        $this->masId = $masId;
        $this->masClientId = $masClientId;
        $this->authenticationSecret = $authenticationSecret;
        $this->masSecret = $masSecret;
        $this->requestJWT();
    }

    /**
     * @param int $masId
     * @param string $masClientId
     * @param string $authenticationSecret
     * @return TriggerdialogService
     */
    public static function makeInstance($masId = 0, $masClientId = '', $authenticationSecret = '', $masSecret)
    {
        if (self::$_instance === null) {
            self::$_instance = new self((int)$masId, $masClientId, $authenticationSecret, $masSecret);
        }

        return self::$_instance;
    }

    public function requestJWT()
    {
        $credentials = [
            "partnerSystemIdExt" => $this->masId,
            "partnerSystemCustomerIdExt" => $this->masClientId,
            "authenticationSecret" => $this->authenticationSecret,
            "locale" => "de"
        ];
        $jwt = $this->client->request('POST', 'authentication/partnersystem/credentialsbased', ["body" => \GuzzleHttp\json_encode($credentials), "debug" => true]);
        $jwt_body = $jwt->getBody()->getContents();
        $jwt_body = \GuzzleHttp\json_decode($jwt_body, true);
        //$jwt_body = $jwt_body["jwtToken"];
        $decoded_jwt_arr = [];
        try {
            $decoded_jwt_arr = JWT::decode($jwt_body, $this->masSecret, array('HS512'));
        } catch (\Exception $e){
            var_dump($e);
        }

        return $decoded_jwt_arr;

    }

    /**
     * @param TriggerCampaign $triggerCampaign
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCampaign(TriggerCampaign $triggerCampaign)
    {
        $data = $this->getCampaignData($triggerCampaign);
        $data['variable'] = $triggerCampaign->getVariablesAsArray();

        $json_body = \GuzzleHttp\json_encode($data);

        $response = $this->client->request('PUT', '/rest-mas/campaign/', ['body' => $json_body]);

        if ($response->getStatusCode() !== 200) {
            throw new RequestException($response, 1569423229);
        }
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     * @param string $state
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateCampaign(TriggerCampaign $triggerCampaign, $state = 'active')
    {
        $data = $this->getCampaignData($triggerCampaign);
        $data['campaignStatus'] = $state;

        $this->transformData($xml, $data);

        $response = $this->client->request('POST', '/rest-mas/campaign/', ['body' => $xml->asXML()]);

        if ($response->getStatusCode() !== 200) {
            throw new RequestException($response, 1569423229);
        }
    }

    public function addCampaignPrintNode()
    {
        // TODO: Implement later on
    }

    /**
     * Not for SSO, should be in another class? TODO
     * @return array
     */
    public function getJWTPayload(): array
    {
        return [
            "partnerSystemIdExt" => $this->coreParametersHelper->get('triggerdialog_partnerSystemIdExt'),
            "partnerSystemCustomerIdExt" => $this->coreParametersHelper->get('triggerdialog_partnerSystemCustomerIdExt'),
            "authenticationSecret" => $this->coreParametersHelper->get('triggerdialog_authenticationSecret'),
            "locale" => $this->coreParametersHelper->get('triggerdialog_locale')
        ];
    }


    /**
     * @param TriggerCampaign $triggerCampaign
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateCampaignVariable(TriggerCampaign $triggerCampaign)
    {
        $data = $this->getCampaignData($triggerCampaign, false);
        $data['variable'] = $triggerCampaign->getVariablesAsArray();

        $xml = new \SimpleXMLElement('<updateCampaignVariableRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></updateCampaignVariableRequest>');
        $this->transformData($xml, $data);

        $response = $this->client->request('POST', '/rest-mas/campaign/variable/', ['body' => $xml->asXML()]);

        if ($response->getStatusCode() !== 200) {
            throw new RequestException($response, 1569423193);
        }
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     * @param Lead $lead
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCampaignTrigger(TriggerCampaign $triggerCampaign, Lead $lead)
    {
        $variables = $triggerCampaign->getVariablesAsArray();
        $variableValue = [];

        foreach ($variables as $variable) {
            $variableValue[] = [
                'name' => $variable['name'],
                'value' => $lead->getFieldValue($variable['name']),
            ];
        }

        $data = $this->getCampaignData($triggerCampaign, false);
        $data['printNodeID'] = $triggerCampaign->getPrintNodeId();
        $data['variableValue'] = $variableValue;
        $json_body = \GuzzleHttp\json_encode($data);
        $xml = new \SimpleXMLElement('<createCampaignTriggerRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></createCampaignTriggerRequest>');
        $this->transformData($xml, $data);

        $response = $this->client->request('POST', '/rest-mas/campaign/campaignTrigger/',
            ['body' => $json_body, 'debug' => true]);

        if ($response->getStatusCode() !== 200) {
            throw new RequestException($response, 1569423375);
        }
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     * @param bool $getFullData
     *
     * @return array
     */
    protected function getCampaignData(TriggerCampaign $triggerCampaign, $getFullData = true)
    {
        $data = [
            'masID' => $this->masId,
            'masCampaignID' => $triggerCampaign->getId(),
            'masClientID' => $this->masClientId,
        ];

        if ($getFullData === false) {
            return $data;
        }

        $data['campaignData'] = [
            'campaignName' => $triggerCampaign->getName(),
            'startDate' => $triggerCampaign->getStartDate()->format('Y-m-d'),
        ];

        $data['printNode'] = [
            'printNodeID' => $triggerCampaign->getPrintNodeId(),
            'description' => $triggerCampaign->getPrintNodeDescription(),
        ];

        if ($triggerCampaign->getEndDate() !== null) {
            $data['campaignData']['endDate'] = $triggerCampaign->getEndDate()->format('Y-m-d');
        }

        return $data;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param array $data
     */
    protected function transformData(\SimpleXMLElement &$xml, $data)
    {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $xml->addChild($key, $value);
            } elseif ($key !== 'variable' && $key !== 'variableValue') {
                $child = $xml->addChild($key);
                $this->transformData($child, $value);
            } elseif ($key === 'variable' && is_array($value)) {
                foreach ($value as $variable) {
                    $child = $xml->addChild('variable');
                    $child->addChild('name', $variable['name']);
                    $child->addChild('type', $variable['type']);
                }
            } elseif ($key === 'variableValue' && is_array($value)) {
                foreach ($value as $variable) {
                    $child = $xml->addChild('variableValue');
                    $child->addChild('name', $variable['name']);
                    $child->addChild('value', $variable['value']);
                }
            }
        }
    }
}
