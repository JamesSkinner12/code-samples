<?php

namespace Edgar\Tools;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Response
{
    protected $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function decoded()
    {
        return (new XmlEncoder())->decode($this->body, 'xml');
    }

    public function contents()
    {
        return $this->decoded();
    }
}