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
        $responseContent = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $error = $responseContent['errors'][0] ?? ['errorMessage' => $responseContent['error']];

        parent::__construct($error['errorMessage'], $code, $previous);
    }
}
