<?php

namespace Modules\Coin\Components\TokenIO;

use InvalidArgumentException;
use Modules\Coin\Components\TokenIO\Exceptions\SignatureVerifyException;
use Modules\Coin\Components\TokenIO\Traits\HasApiMethod;
use Modules\Coin\Components\TokenIO\Traits\HasHttpRequest;
use Psr\Http\Message\RequestInterface;


class TokenIO
{
    /**
     * @var int 对接内容 超时时间
     */
    public $timeout = 3600;

    use HasHttpRequest,
        HasApiMethod;

    /**
     * @var string
     */
    protected $host = 'https://ts.bzhitu.com';
    /**
     * @var string
     */
    protected $key;
    /**
     * @var string
     */
    protected $secret;

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getUri($urn)
    {
        $urn = ltrim($urn, '/');

        return $urn == 'ping' ? '/' . $urn : '/api/' . $urn;
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     * @throws SignatureVerifyException
     */
    public function verifyViaRequest(RequestInterface $request)
    {
        $rawBody = $request->getBody();

        $uri = $request->getUri();
        $this->verifySignature(
            $request->getHeaderLine('api-key'),
            $request->getHeaderLine('api-nonce'),
            $request->getHeaderLine('api-signature'),
            $rawBody,
            str_replace($uri->getScheme() . '://' . $uri->getHost(), '', $uri), // 去掉host
            $request->getMethod()
        );

        return json_decode($rawBody, true);
    }

    /**
     * @param string $apiKey
     * @param string $apiNonce
     * @param string $apiSignature
     * @param string $rawBody
     * @param string $uri 不带host
     * @param string $method
     *
     * @return bool
     * @throws SignatureVerifyException
     */
    public function verifySignature(
        string $apiKey,
        string $apiNonce,
        string $apiSignature,
        string $rawBody,
        string $uri,
        string $method
    ) {
        list($timestamp) = explode('-', $apiNonce);
        if ($timestamp < time() - $this->timeout) {
            throw new InvalidArgumentException('Connecting data is out of date');
        }

        $auth = $auth = $method . $uri . $apiNonce . $rawBody;

        $localSignature = hash_hmac('sha256', $auth, $this->secret, false);

        if ($this->key !== $apiKey || $localSignature != $apiSignature) {
            throw new SignatureVerifyException('Signature verify failed');
        }

        return true;
    }
}
