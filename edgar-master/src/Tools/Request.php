<?php

namespace Edgar\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Request
{
    const SEC_URL = 'https://www.sec.gov/';

    public function client()
    {
        return new Client([
            'base_uri' => self::SEC_URL,
            'timeout' => 10.0
        ]);
    }

    /**
     * @return $this
     */
    public static function collect()
    {
        $class = get_called_class();
        return new $class();
    }

    public function response(Response $contents)
    {
        return $contents->getBody()->__toString();
    }

    public function request($endpoint, $method = "GET", $params = [])
    {
        $contents = $this->client()->request($method, $endpoint, $params);
        return $this->response($contents);
    }
}