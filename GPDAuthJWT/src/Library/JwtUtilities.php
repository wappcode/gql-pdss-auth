<?php

namespace GPDAuthJWT\Library;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GPDAuth\Entities\PermissionAccess;
use GPDAuth\Models\ResourcePermission;
use GPDAuthJWT\Models\UnverifiedJWT;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class JwtUtilities
{



    public static function extractJWTFromRequest(ServerRequestInterface $request): ?string
    {
        $auth = $request->getHeaderLine('Authorization');

        if (!str_starts_with($auth, 'Bearer ')) {
            return null;
        }

        $jwt = substr($auth, 7);
        return $jwt;
    }

    /**
     * Convierte los scopes del JWT en permisos de recurso
     *
     * @param array $claims
     * @return array array<ResourcePermission>
     */
    public static function convertScopesToPermissions(array $claims): array
    {
        $scopes = $claims['scope'] ?? $claims['scp'] ?? '';
        if (empty($scopes) || !is_string($scopes)) {
            return [];
        }
        $jwtScopes = explode(' ', $scopes);

        $permissions = array_map(function (string $scope) {
            $scopeFormated = str_replace('.', ':', strtolower($scope));
            [$resource, $permissionValue] = explode(':', $scopeFormated, 2);
            $permission = new ResourcePermission($resource, PermissionAccess::ALLOW->value, $permissionValue);
            return $permission;
        }, $jwtScopes);

        return $permissions;
    }

    public static function decodeUnverified(string $jwt): UnverifiedJWT
    {
        [$h, $p] = explode('.', $jwt);
        return new UnverifiedJWT(
            JWT::jsonDecode(JWT::urlsafeB64Decode($h)),
            JWT::jsonDecode(JWT::urlsafeB64Decode($p))
        );
    }

    public static function parsePublicKeyFromJWK(array $jwk): ?Key
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
     * @param Key $secureKey
     * @param string $algorithm
     * @return object|null
     */
    public static function decodeAndVerify(string $token, Key $secureKey,  ?stdClass &$headers = null): ?object
    {
        if (empty($secureKey)) {
            throw new Exception("Empty jwt secure key");
        }
        if (empty($token)) {
            return null;
        }
        $data = JWT::decode($token, $secureKey, $headers);
        return $data;
    }

    /**
     * Crea un key para usar en la decodificación y verificación de un JWT
     *
     * @param string $secure
     * @param string $alg
     * @return Key
     */
    public static function createKey(string $secure, string $alg): Key
    {
        return new Key($secure, $alg);
    }


    public static function extractRoles(array $claims): array
    {
        $roles = [];

        // formato simple
        if (isset($claims['roles'])) {
            $roles = array_merge($roles, $claims['roles']);
        }

        // keycloak realm roles
        if (isset($claims['realm_access']['roles'])) {
            $roles = array_merge($roles, $claims['realm_access']['roles']);
        }

        // keycloak resource roles
        if (isset($claims['resource_access'])) {
            foreach ($claims['resource_access'] as $resource) {
                if (isset($resource['roles'])) {
                    $roles = array_merge($roles, $resource['roles']);
                }
            }
        }
        return array_unique($roles);
    }
}
