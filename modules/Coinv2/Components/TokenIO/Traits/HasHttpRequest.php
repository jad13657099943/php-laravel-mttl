<?php

namespace Modules\Coinv2\Components\TokenIO\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait HasHttpRequest
{

    public function send($urn, $params = [], $method = 'GET')
    {
        $method = strtoupper($method);
        $client = new Client([
            //'base_uri' => $this->host,
            'timeout' => $this->timeout,
        ]);

        //java团队null值不要传过去
        foreach ($params as $key => $item) {
            if (is_null($item)) {
                unset($params[$key]);
            }
        }
        $options = [];

        //$uri = $this->getUri($urn);
        $uri = $this->host . $urn;

        switch (strtoupper($method)) {
            case 'GET':
            case 'DELETE':
                if (!empty($params)) {
                    $uri .= (stripos($uri,
                            '?') === false ? '?' : '&') . (is_array($params) ? http_build_query($params) : '');
                }
                break;

            default:
                if (is_array($params)) {
                    //$options['json'] = $params;
                    //注意是用from表单方式提交，不要用json
                    $options['form_params'] = $params;
                } else {
                    $options['body'] = $params;
                }
        }

        $options['headers'] = $this->getHeaders($uri, $params, $method);

        $urlData = [
            'uri'=>$uri,
        ];
        \Log::alert('请求数据', ['params' => $params, 'option' => $options,'urlData'=>$urlData]);
        $response = $client->request($method, $uri, $options);


        $result = $this->unwrapResponse($response);
        if ($result['code'] && $result['code'] != 0) {
            $msg = $result['message'] ?? "未返回message信息";
            $msg = "TokenioV2错误：" . $msg;
            //写入日志：
            \Log::alert($msg, ['params' => $params, 'option' => $options,'res'=>$result]);
        }
        return $result;
    }

    protected function getHeaders($uri, array $params = [], $method = 'GET')
    {
        $payload = in_array(strtoupper($method), ['GET', 'DELETE']) ? null : json_encode($params);
        $key = $this->key;
        $nonce = time() . '-' . rand(1, 100000);
        $message = strtoupper($method) . $uri . $nonce . $payload;
        //$signature = hash_hmac('sha256', $message, $this->secret, false);
        //暂时不验
        $signature = "94b62ef9ccad9d679761a1608cfa81b8b7f2a4b6861b4e382c76bc543f3a5e17";
        return [
            'api-nonce' => $nonce,
            'api-key' => $key,
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
