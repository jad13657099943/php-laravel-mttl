<?php

namespace Modules\Coin\Components\TokenIO\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait HasHttpRequest
{

    public function send($urn, $params = [], $method = 'GET')
    {
        $method = strtoupper($method);
        $client = new Client([
            'base_uri' => $this->host,
            'timeout'  => $this->timeout,
        ]);

        $options = [];

        $uri = $this->getUri($urn);

        switch (strtoupper($method)) {
            case 'GET':
            case 'DELETE':
                if(!empty($params)) {
                    $uri .= (stripos($uri,
                            '?') === false ? '?' : '&') . (is_array($params) ? http_build_query($params) : '');
                }
                break;

            default:
                if (is_array($params)) {
                    $options['json'] = $params;
                } else {
                    $options['body'] = $params;
                }
        }

        $options['headers'] = $this->getHeaders($uri, $params, $method);

        $response = $client->request($method, $uri, $options);

        $result = $this->unwrapResponse($response);

        return $result;
    }

    protected function getHeaders($uri, array $params = [], $method = 'GET')
    {
        $payload = in_array(strtoupper($method), ['GET', 'DELETE']) ? null : json_encode($params);
        $key = $this->key;
        $nonce = time() . '-' . rand(1, 100000);
        $message = strtoupper($method) . $uri . $nonce . $payload;
        $signature = hash_hmac('sha256', $message, $this->secret, false);

        return [
            'api-nonce'     => $nonce,
            'api-key'       => $key,
            'api-signature' => $signature,
        ];
    }

    /**
     * Convert response contents to json.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return ResponseInterface|array|string
     */
    protected function unwrapResponse(ResponseInterface $response)
    {
//        $contentType = $response->getHeaderLine('Content-Type');
        $result = json_decode($response->getBody(), true);

        return $result;
    }
}
