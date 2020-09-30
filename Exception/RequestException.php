<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class RequestException extends \Exception
{
    /**
     * RequestException constructor.
     *
     * @param ResponseInterface $response
     * @param int               $code
     * @param Throwable|null    $previous
     */
    public function __construct(ResponseInterface $response, $code = 0, Throwable $previous = null)
    {
        $responseContent = $response->getBody()->getContents();

        if (strpos($responseContent, '<') !== 0) {
            parent::__construct($response->getReasonPhrase(), $code, $previous);
        } else {
            $xml = new \SimpleXMLElement();
            parent::__construct($xml->errorMessage, $code, $previous);
        }
    }
}
