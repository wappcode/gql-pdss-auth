<?php

namespace GPDAuthJWT\Contracts;

interface JWTKidRepositoryInterface
{
    public function getKeyByKid(string $kid): ?\GPDAuthJWT\Entities\JWTKey;
}
