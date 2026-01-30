<?php

namespace GPDAuthJWT\Models;

use GPDAuthJWT\Entities\TrustedIssuer;

interface JWTTrustIssuerRepositoryInterface
{
    public function findIssuer(string $issuer): ?TrustedIssuer;

    public function fetchJWKByKid(TrustedIssuer $issuer, string $keyId): ?array;

    public function isValidAudience(TrustedIssuer $issuer, string $audience): bool;
}
