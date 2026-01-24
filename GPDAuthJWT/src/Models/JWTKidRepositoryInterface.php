<?php

namespace GPDAuthJWT\Models;

interface JWTKidRepositoryInterface
{
    public function getKeyByKid(string $kid): ?\GPDAuthJWT\Entities\JWTKey;
}
