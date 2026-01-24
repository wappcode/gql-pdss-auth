<?php

namespace GPDAuth\Library;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GPDAuthJWT\Models\UnverifiedJWT;
use stdClass;

class AuthJWTManager
{


    /**
     * Retrive JWT from headers or GET Request
     *
     * @param string $getKey
     * @param string $header
     * @return string|null
     */
    public static function retriveJWT($getKey = "Authorization", $header = "Authorization"): ?string
    {
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authorizationHeader)) {
            $authorizationHeader = $_SERVER[$header] ?? $_SERVER[strtolower($header)] ?? '';
        }
        if (empty($authorizationHeader)) {
            $apacheHeaders = apache_request_headers();
            $authorizationHeader = $apacheHeaders[$header] ?? $apacheHeaders[strtolower($header)] ?? '';
        }
        if (empty($authorizationHeader)) {
            $authorizationHeader = $_GET[$getKey] ?? $_GET[strtolower($getKey)] ?? '';
        }

        if (!preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return null;
        }
        $jwt = $matches[1] ?? null;
        if (empty($jwt)) {
            return null;
        }
        return $jwt;
    }

    public static function decodeWithoutVerification(string $jwt): UnverifiedJWT
    {
        [$h, $p] = explode('.', $jwt);
        return new UnverifiedJWT(
            JWT::jsonDecode(JWT::urlsafeB64Decode($h)),
            JWT::jsonDecode(JWT::urlsafeB64Decode($p))
        );
    }

    public static function getPublicKeyFromJWK(array $jwk): ?Key
    {
        $kid = $jwk['kid'] ?? null;
        if (empty($kid)) {
            return null;
        }
        $publicKeys = JWK::parseKeySet(['keys' => [$jwk]]);

        return $publicKeys[$kid] ?? null;
    }



    /**
     * Decodificar y verificar un JWT
     *
     * @param string $token
     * @param Key|ArrayAccess<string, Key>|array<string, Key> $keyOrKeyArray
     * @param string $algorithm
     * @return object|null
     */
    public static function decode(string $token,  $secureKey, string $algorithm = 'RS256', ?stdClass &$headers = null): ?object
    {
        if (empty($secureKey)) {
            throw new Exception("Empty jwt secure key");
        }
        if (empty($token)) {
            return null;
        }
        $data = JWT::decode($token, new Key($secureKey, $algorithm), $headers);
        return $data;
    }
    /**
     * Create a JWT 
     *
     * @param array $session
     * @param string $secureKey
     * @param string $algorithm
     * @return string
     */
    public static function createToken(array $session,  string $secureKey, string $algorithm = 'HS256'): string
    {
        $payload = $session;
        $jwt = JWT::encode($payload, $secureKey, $algorithm);
        return $jwt;
    }

    public static function addJWTToHeader(string $token, string $header = "Authorization"): void
    {
        header("{$header}: Bearer {$token}");
    }

    public static function getISSNoVerified(string $jwt): ?string
    {

        if (empty($jwt)) {
            return null;
        }
        $sections = explode(".", $jwt);
        if (!isset($sections[1]) || empty($sections[1])) {
            return null;
        }
        $jwtDataStr = base64_decode($sections[1]);
        $jwtData = json_decode($jwtDataStr, true);
        return $jwtData["iss"] ?? null;
    }
}
