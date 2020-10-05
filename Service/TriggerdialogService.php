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
    const AUDIENCE = 'https://dm-uat.deutschepost.de'; //TODO change for real address

    const TEST_AUDIENCE = 'https://dm-uat.deutschepost.de';

    /**
     * @var self
     */
    protected static $_instance;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $partnerSystemIdExt;

    /**
     * @var string
     */
    protected $authenticationSecret;

    /**
     * @var string
     */
    protected $partnerSystemCustomerIdExt;

    /**
     * @var array
     */
    protected $jwtKeys;

    /**
     * @var string
     */
    protected $jwt;

    /**
     * @var array
     */
    protected $config = [
        'http_errors' => false,
        'headers' => [
            'Content-Type' => 'application/xml; charset=utf-8',
        ],
    ];

    /**
     * TriggerdialogService constructor.
     *
     * @param array $config
     * @param int $masId
     * @param string $masClientId
     */
    protected function __construct($config, $masId, $masClientId, $authenticationSecret)
    {
        $audience = MAUTIC_ENV === 'prod' ? self::AUDIENCE : self::TEST_AUDIENCE;
        $config = $config + $this->config + ['base_uri' => $audience];
        $this->client = new Client($config);
        $this->partnerSystemIdExt = $masId;
        $this->partnerSystemCustomerIdExt = $masClientId;
        $this->authenticationSecret = $authenticationSecret;
        $this->setJWT();
    }

    /**
     * @param array $config
     * @param int $masId
     * @param string $masClientId
     *
     * @return TriggerdialogService
     */
    public static function makeInstance($config = [], $masId = 0, $masClientId = '', $authenticationSecret = '')
    {
        if (self::$_instance === null) {
            self::$_instance = new self($config, (int)$masId, $masClientId, $authenticationSecret);
        }

        if (self::$_instance->getJwt() === null) {
            self::$_instance->setJWT();
        }

        return self::$_instance;
    }

    public function setJWT()
    {
        $this->jwt = $this->getAutorizationJWT();
        $this->jwtKeys = JWT::decode($this->jwt, $this->authenticationSecret);
    }

    public function getAutorizationJWT()
    {
        $credentials = [
            "partnerSystemIdExt" => (string)$this->partnerSystemIdExt,
            "partnerSystemCustomerIdExt" => $this->partnerSystemCustomerIdExt,
            "authenticationSecret" => $this->authenticationSecret,
            "locale" => "de"
        ];
        //$cred = \GuzzleHttp\json_encode($credentials);
        return $this->client->request(
            'POST',
            '/gateway/authentication/partnersystem/credentialsbased',
            [
                'debug'=> true,
                'JSON' => $credentials
            ]
        );
    }

    public function reauthJWT()
    {
        //TODO still needs bearer token
        return $this->client->request(
            'POST',
            '/gateway/authentication/reauth'
        );
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
        $json_body = $data;
        $xml = new \SimpleXMLElement('<createCampaignRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></createCampaignRequest>');
        $this->transformData($xml, $data);

        $response = $this->client->request('PUT', '/gateway/longtermcampaigns', ['json' => $json_body]);

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

        $xml = new \SimpleXMLElement('<updateCampaignRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></updateCampaignRequest>');
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

        $xml = new \SimpleXMLElement('<createCampaignTriggerRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></createCampaignTriggerRequest>');
        $this->transformData($xml, $data);

        $response = $this->client->request('POST', '/rest-mas/campaign/campaignTrigger/', ['body' => $xml->asXML()]);

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
            'masID' => $this->partnerSystemIdExt,
            'masCampaignID' => $triggerCampaign->getId(),
            'masClientID' => $this->partnerSystemCustomerIdExt,
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

    /**
     * @return string
     */
    public function getJwt(): string
    {
        return $this->jwt;
    }


}
