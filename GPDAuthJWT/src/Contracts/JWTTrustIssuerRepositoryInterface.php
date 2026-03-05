<?php

namespace GPDAuthJWT\Contracts;


interface JWTTrustIssuerRepositoryInterface
{
    public function isTrustedIssuer(string $issuer): bool;

    public function fetchJsonWebKeyByKeyId(string $issuer, string $keyId): ?array;
    public function getIssuerAlgorithm(string $issuer): ?string;
    public function isValidAudience(string $issuer, string $audience): bool;
    public function getAllowedRolesForIssuer(string $issuer, array $roles): array;
}
