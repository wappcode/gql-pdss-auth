<?php

namespace GPDAuthJWT\Models;


class UnverifiedJWT
{
    private object $payload;
    private object $header;

    public function __construct(object $header, object $payload)
    {
        $this->header = $header;
        $this->payload = $payload;
    }
    public function getPayload(): object
    {
        return $this->payload;
    }

    public function getHeader(): object
    {
        return $this->header;
    }
}
