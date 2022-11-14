<?php

namespace GPDAuth\Library;

use DateTime;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthJWTManager
{


    public static function getTokenFromAuthoriaztionHeader(): ?string
    {
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return null;
        }
        $jwt = $matches[1] ?? null;
        if (empty($jwt)) {
            return null;
        }
        return $jwt;
    }

    public static function getTokenData(string $token, string $secureKey, string $algorithm = 'HS256'): ?array
    {
        if (empty($secureKey)) {
            throw new Exception("Empty jwt secure key");
        }
        if (empty($token)) {
            return null;
        }
        $data = JWT::decode($token, new Key($secureKey, $algorithm));
        $data_array = (array) $data;
        return $data_array;
    }
    /**
     *
     * @param array $user
     * @param integer $expirationTime tiempo para que expire en segundos
     * @return string
     */
    public static function createUserToken(array $user, string $secureKey, int $expirationTime, string $algorithm = 'HS256'): string
    {
        $issuedAt = new DateTimeImmutable();
        $serverName = $_SERVER["SERVER_NAME"];
        $expire = new DateTime();
        $expire->modify("+{$expirationTime} seconds");
        $username = $user["username"];
        $payload = [

            'iat'  => $issuedAt->getTimestamp(),         // Issued at
            'iss'  => $serverName,                       // Issuer
            'nbf'  => $issuedAt->getTimestamp(),         // Not before
            'exp'  => $expire->getTimestamp(),                           // Expire
            'preferred_username' => $username,
        ];
        $jwt = JWT::encode($payload, $secureKey, $algorithm);
        return $jwt;
    }

    public static function addTokenToResponseHeader(string $token): void
    {
        header("Authorization: Bearer {$token}");
    }
}
