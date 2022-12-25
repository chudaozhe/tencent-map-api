<?php

namespace DeathSatan\TencentMapApi;

use DeathSatan\TencentMapApi\Exception\ServerException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * api操作类
 */
final class Request
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret_key;

    /**
     * @var Client
     */
    protected $client;

    protected $response;

    public function __construct($key, $secret_key, Client $client)
    {
        $this->key = $key;
        $this->secret_key = $secret_key;
        $this->client = $client;
    }

    /**
     * 发送get请求
     * @throws GuzzleException
     */
    public function get($url, $data=[]): Response
    {
        return $this->beforeResponse(
            $this->client->request('get',$url,[
                'query'=>$this->signData($url, $data),
            ])
        );
    }

    /**
     * 请求后前置处理response
     * @param \GuzzleHttp\Psr7\Response $response
     * @return Response
     */
    protected function beforeResponse(\GuzzleHttp\Psr7\Response $response): Response
    {
        $this->response = new Response($response);
        if ($this->response['status']!==0){
            throw new ServerException($this->response['message']);
        }
        return $this->response();
    }

    /**
     * 获取相应
     * @return Response
     */
    private function response(): Response
    {
        return $this->response;
    }

    /**
     * 数据签名
     * @param string $url
     * @param array $data
     * @return array
     */
    private function signData(string $url, array $data): array
    {
        $data=array_merge($data,[
            'key'=>$this->key
        ]);
        ksort($data);
        $str='/ws/'.$url.'?'.urldecode(http_build_query($data)).$this->secret_key;
        $data['sig']=md5($str);
        return $data;
    }
}