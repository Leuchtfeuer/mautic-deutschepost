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
use Pheanstalk\Exception;

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
            'Content-Type' => 'application/json; charset=utf-8',
        ],
    ];

    /**
     * TriggerdialogService constructor.
     *
     * @param array $config
     * @param int $masId
     * @param string $masClientId
     * @param mixed $authenticationSecret
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
     * @param mixed $authenticationSecret
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
        $this->jwt = $this->getAuthorizationJWT();
        try {
            $this->jwtKeys = json_decode(json_encode(JWT::decode($this->jwt, 'aKaqioatnPqwSrWWy5-9v', ['HS512'])), true);
        } catch (\Exception $e) {
            var_dump($e); //todo: log exception
        }
    }

    public function getAuthorizationJWT()
    {
        $credentials = [
            'partnerSystemIdExt' => (string)$this->partnerSystemIdExt,
            'partnerSystemCustomerIdExt' => $this->partnerSystemCustomerIdExt,
            'authenticationSecret' => $this->authenticationSecret,
            'locale' => 'de',
        ];

        $result = $this->client->request(
            'POST',
            '/gateway/authentication/partnersystem/credentialsbased',
            [
                'json' => $credentials,
            ]
        );

        return json_decode($result->getBody()->getContents(), true)['jwtToken'];
    }

    public function reauthJWT()
    {
        return $this->client->request(
            'POST',
            '/gateway/authentication/reauth',
            ['headers' => [
                'authorization' => 'Bearer ' . $this->jwt,
            ]]
        );
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCampaign(TriggerCampaign $triggerCampaign): TriggerCampaign
    {
        $data = $this->getCampaignData($triggerCampaign);
        $xml = new \SimpleXMLElement('<createCampaignRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></createCampaignRequest>');
        $this->transformData($xml, $data);

        $response = $this->client->request(
            'POST',
            '/gateway/longtermcampaigns',
            [
                'json' => $data,
                'headers' => ['Authorization' => $this->jwt],
            ]
        );

        $responseBody = json_decode($response->getBody()->getContents(), true);
        $triggerCampaign->setTriggerId($responseBody['id']);

        if ($response->getStatusCode() >= 300) {
            throw new RequestException($response, 1569423229);
        }

        $this->createMailing($triggerCampaign);
        $this->setVariableDefinitions($triggerCampaign);

        return $triggerCampaign;
    }

    public function setVariableDefinitions(TriggerCampaign $triggerCampaign): void
    {
        $jsonBody = [
            'customerId' => $this->jwtKeys['customerIds'][0],
            'createVariableDefRequestRepList' => $triggerCampaign->getVariablesAsArray(),
        ];

        $response = $this->client->request(
            'POST',
            '/gateway/mailings/' . $triggerCampaign->getMailingId() . '/variabledefinitions',
            [
                'json' => $jsonBody,
                'headers' => ['Authorization' => $this->jwt],
            ]
        );
        if ($response->getStatusCode() >= 300) {
            throw new RequestException($response, 1569423229);
        }
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     */
    public function createMailing(TriggerCampaign $triggerCampaign): void
    {
        $jsonBody = [
            'customerId' => $this->jwtKeys['customerIds'][0],
            'campaignId' => $triggerCampaign->getTriggerId(),
        ];

        $response = $this->client->request(
            'POST',
            '/gateway/mailings',
            [
                'json' => $jsonBody,
                'headers' => ['Authorization' => $this->jwt],
            ]
        );

        $responseBody = json_decode($response->getBody()->getContents(), true);
        $triggerCampaign->setMailingId($responseBody['id']);
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     * @param string $state
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateCampaign(TriggerCampaign $triggerCampaign, $state = 'active'): void
    {
        $data = $this->getCampaignData($triggerCampaign);
        $jsonBody = $data;
        $xml = new \SimpleXMLElement('<createCampaignRequest xmlns:ns2="urn:pep-dpdhl-com:triggerdialog/campaign/v_10"></createCampaignRequest>');
        $this->transformData($xml, $data);

        $response = $this->client->request(
            'PUT',
            '/gateway/longtermcampaigns',
            [
                'json' => $jsonBody,
                'headers' => ['Authorization' => $this->jwt],
            ]
        );
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     *
     * @param null $diff
     * @throws RequestException
     */
    public function updateCampaignVariable(TriggerCampaign $triggerCampaign, $diff = null): void
    {
        $variables = $this->transformVariableArray($diff);
        $jsonBody = [
            'customerId' => $this->jwtKeys['customerIds'][0],
            'updateVariableDefRequestRepList' => $variables,
        ];
        $triggerCampaign->getVariablesAsArray();
        $response = $this->client->request(
            'PUT',
            '/gateway/mailings/' . $triggerCampaign->getMailingId() . '/variabledefinitions',
            [
                'json' => $jsonBody,
                'headers' => ['Authorization' => $this->jwt],
            ]
        );
        if ($response->getStatusCode() >= 300) {
            throw new RequestException($response, 1569423229);
        }
    }

    protected function transformVariableArray($variables_old)
    {

        $variables = [];

        foreach ($variables_old as $variable) {
            $typeDef = '';

            switch ($variable['variable']) {
                case "string":
                    $typeDef = 10;
                    break;
                case "integer":
                    $typeDef = 20;
                    break;
                case "boolean":
                    $typeDef = 30;
                    break;
                case "date":
                    $typeDef = 40;
                    break;
                case "image":
                    $typeDef = 50;
                    break;
                case "imageurl":
                    $typeDef = 60;
                    break;
                case "float":
                    $typeDef = 70;
                    break;
                case "zip":
                    $typeDef = 80;
                    break;
                case "countryCode":
                    $typeDef = 90;
                    break;
            }

            $variables[] = [
                'label' => $variable['field'],
                'sortOrder' => 0,
                'dataTypeId' => $typeDef,
            ];
        }

        return $variables;
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     * @param Lead $lead
     *
     * @throws RequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCampaignTrigger(TriggerCampaign $triggerCampaign, Lead $lead): void
    {
        $variables = $triggerCampaign->getVariablesAsArray();
        $variableValue = [
            'campaignId' => $triggerCampaign->getTriggerId(),
            'customerId' => $this->jwtKeys['customerIds'][0],
        ];
        $address_array = ['recipientData' => [], 'recipientIdExt' => $lead->getId()];

        foreach ($variables as $variable) {
            $address_array['recipientData'][] = [
                'label' => $variable['label'],
                'value' => $lead->getFieldValue($variable['label']),
            ];
        }
        $variableValue['recipients'][] = $address_array;

        $response = $this->client->request(
            'POST',
            '/gateway/recipients/',
            [
                'json' => $variableValue,
                'headers' => ['Authorization' => $this->jwt],
            ]
        );
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
    protected function getCampaignData(TriggerCampaign $triggerCampaign, $getFullData = true): array
    {
        $customer = $this->jwtKeys['customerIds'][0];
        $data = [
            'campaignIdExt' => $triggerCampaign->getId(),
            'campaignName' => $triggerCampaign->getName(),
            'customerId' => (string)$customer,
            'startDate' => $triggerCampaign->getStartDate()->format('Y-m-d'),
        ];

        if ($triggerCampaign->getEndDate() !== null) {
            $data['endDate'] = $triggerCampaign->getEndDate()->format('Y-m-d');
        }

        return $data;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param array $data
     */
    protected function transformData(\SimpleXMLElement &$xml, $data): void
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

    /**
     * @return bool
     */
    public function isTokenValid(): bool
    {
        return $this->jwtKeys['exp'] < strtotime('now - 5 minutes');
    }
}
