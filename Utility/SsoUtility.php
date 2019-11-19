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
    public function isValid()
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
    protected function validateParameter($name)
    {
        return $this->coreParametersHelper->hasParameter($name) || !empty($this->coreParametersHelper->getParameter($name));
    }

    public function generateJWT()
    {
        $payload = [
            'iss' => self::PAYLOAD_ISS,
            'iat' => time(),
            'exp' => strtotime('+30 day', time()),
            'masId' => $this->coreParametersHelper->getParameter('triggerdialog_masId'),
            'masClientId' => $this->coreParametersHelper->getParameter('triggerdialog_masClientId'),
            'username' => $this->coreParametersHelper->getParameter('triggerdialog_username'),
            'email' => $this->coreParametersHelper->getParameter('triggerdialog_email'),
            'firstname' => $this->coreParametersHelper->getParameter('triggerdialog_firstName'),
            'lastname' => $this->coreParametersHelper->getParameter('triggerdialog_lastName'),
        ];

        $this->JWT = JWT::encode($payload, $this->coreParametersHelper->getParameter('triggerdialog_masSecret'), 'HS512');
    }

    /**
     * @return string
     */
    public function getSSOUrl()
    {
        $audience = MAUTIC_ENV === 'prod' ? self::SSO_AUDIENCE : self::SSO_TEST_AUDIENCE;

        return sprintf('%s?jwt=%s', $audience, $this->JWT);
    }
}
