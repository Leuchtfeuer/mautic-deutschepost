<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Utility;

if (!class_exists('Firebase\JWT\JWT', false)) {
    require_once __DIR__ . '/../Library/vendor/autoload.php';
}

use Firebase\JWT\JWT;
use Mautic\CoreBundle\Helper\CoreParametersHelper;

class SsoUtility
{
    const SSO_AUDIENCE = 'https://login.triggerdialog.de/triggerdialog/sso/auth';
    const SSO_TEST_AUDIENCE = 'https://triggerdialog-uat.dhl.com/triggerdialog/sso/auth';

    const PAYLOAD_ISS = 'bitmotion';

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;

    /**
     * @var string[]
     */
    protected $errors = [];

    /**
     * @var string
     */
    protected $JWT = '';

    /**
     * SsoUtility constructor.
     */
    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $valid = true;
        $parameters = [
            'triggerdialog_masId',
            'triggerdialog_masClientId',
            'triggerdialog_username',
            'triggerdialog_email',
            'triggerdialog_firstName',
            'triggerdialog_lastName',
        ];

        foreach ($parameters as $parameter) {
            if ($this->validateParameter($parameter) === false) {
                $this->errors[] = $parameter;
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function validateParameter($name): bool
    {
        return $this->coreParametersHelper->has($name) || !empty($this->coreParametersHelper->get($name));
    }

    public function generateJWT(): void
    {
        $payload = [
            'iss' => self::PAYLOAD_ISS,
            'iat' => time(),
            'exp' => strtotime('+30 day', time()),
            'masId' => $this->coreParametersHelper->get('triggerdialog_masId'),
            'masClientId' => $this->coreParametersHelper->get('triggerdialog_masClientId'),
            'username' => $this->coreParametersHelper->get('triggerdialog_username'),
            'email' => $this->coreParametersHelper->get('triggerdialog_email'),
            'firstname' => $this->coreParametersHelper->get('triggerdialog_firstName'),
            'lastname' => $this->coreParametersHelper->get('triggerdialog_lastName'),
        ];

        $this->JWT = JWT::encode($payload, $this->coreParametersHelper->get('triggerdialog_masSecret'), 'HS512');
    }

    /**
     * @return string
     */
    public function getSSOUrl(): string
    {
        $audience = MAUTIC_ENV === 'prod' ? self::SSO_AUDIENCE : self::SSO_TEST_AUDIENCE;

        return sprintf('%s?jwt=%s', $audience, $this->JWT);
    }
}
