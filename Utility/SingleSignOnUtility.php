<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Utility;

if (!class_exists('Firebase\JWT\JWT', false)) {
    require_once dirname(__DIR__) . '/Library/vendor/autoload.php';
}

use Firebase\JWT\JWT;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\UserHelper;
use MauticPlugin\MauticTriggerdialogBundle\Helper\AudienceHelper;

class SingleSignOnUtility
{
    const SSO_URL = '%s?partnersystem=%s';

    const PAYLOAD_ISS = 'issuer';

    const REQUIRED_PARAMETERS = [
        'triggerdialog_masId',
        'triggerdialog_masClientId',
        'triggerdialog_masSecret',
    ];

    protected $coreParametersHelper;

    protected $user;

    public function __construct(CoreParametersHelper $coreParametersHelper, UserHelper $userHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->user = $userHelper->getUser();
    }

    public function getSingleSignOnUrl(): string
    {
        if ($this->isValid()) {
            return sprintf(self::SSO_URL, AudienceHelper::getAudience(), $this->generateJsonWebToken());
        }

        return '';
    }

    protected function isValid(): bool
    {
        // TODO: Log these errors
        $errors = [];

        foreach (static::REQUIRED_PARAMETERS as $parameter) {
            if ($this->validateParameter($parameter) === false) {
                $errors[] = $parameter;
            }
        }

        return empty($errors);
    }

    protected function validateParameter(string $name): bool
    {
        return $this->coreParametersHelper->has($name) && !empty($this->coreParametersHelper->get($name));
    }

    protected function generateJsonWebToken(): string
    {
        return JWT::encode(
            [
                'firstname' => $this->user->getFirstName(), // TODO: Validate this (required, max 50 chars)
                'lastname' => $this->user->getLastName(), // TODO: Validate this (required, max 50 chars)
                'email' => $this->user->getEmail(), // TODO: Validate this (required, max 150 chars)
                'username' => $this->user->getUsername(), // TODO: Validate this (required, max 80 chars)
                'masClientId' => $this->coreParametersHelper->get('triggerdialog_masClientId'),
                'masId' => (int)$this->coreParametersHelper->get('triggerdialog_masId'),
                'iss' => self::PAYLOAD_ISS,
                'exp' => strtotime('+30 day', time()),
                'iat' => time(),
            ],
            $this->coreParametersHelper->get('triggerdialog_masSecret'),
            'HS512'
        );
    }
}
