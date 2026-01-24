<?php

namespace GPDAuthJWT\Models;

use GPDAuthJWT\Entities\TrustedIssuer;
use GPDAuthJWT\Entities\TrustedIssuers;

interface JWTTrustIssuerRepositoryInterface
{
    public function findIssuer(string $issuer): ?TrustedIssuers;

    public function getJWKFromIssuer(TrustedIssuer $issuer, string $keyId): ?array;

    public function validateAudience(TrustedIssuer $issuer, string $audience): bool;
}
