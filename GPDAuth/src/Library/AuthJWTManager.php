<?php

namespace GPDAuth\Library;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    /**
     * Retrive JWT data (payload) before validate security.
     * Be careful
     */
    public static function getJWTDataNoValidation(string $jwt): array
    {
        list($header, $payload, $signature) = explode('.', $jwt);
        $jsonToken = base64_decode($payload);
        $data = json_decode($jsonToken, true);
        return $data;
    }

    public static function getJWTData(string $token, string $secureKey, string $algorithm = 'HS256'): ?array
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
