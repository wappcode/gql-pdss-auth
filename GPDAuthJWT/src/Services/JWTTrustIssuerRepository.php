<?php

namespace GPDAuthJWT\Services;

use GPDAuthJWT\Entities\TrustedIssuer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;
use GPDAuth\Entities\Role;
use GPDAuthJWT\Entities\TrustedIssuerAudience;
use GPDAuthJWT\Contracts\JWTTrustIssuerRepositoryInterface;
use GPDCore\Contracts\AppContextInterface;

class JWTTrustIssuerRepository implements JWTTrustIssuerRepositoryInterface
{


    public function __construct(private AppContextInterface $context) {}


    public function findIssuer(string $issuer): ?TrustedIssuer
    {
        $entityManager = $this->context->getEntityManager();
        $issuer = $entityManager->createQueryBuilder()->from(TrustedIssuer::class, 'ti')
            ->leftJoin('ti.allowedRoles', 'r')
            ->select(['ti', 'r'])
            ->where('ti.issuer = :issuer')
            ->andWhere('ti.status = :status')
            ->setParameter('issuer', $issuer)
            ->setParameter('status', 'active')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $issuer;
    }

    /** TODO: Agregar cache */
    public function fetchJWKByKid(TrustedIssuer $issuer, string $keyId): ?array
    {
        if (!$issuer || $issuer->getStatus() !== 'active') {
            return null;
        }

        $jwksUrl = $issuer->getJwksUrl();

        try {
            // Crear cliente HTTP con Guzzle
            $client = new Client([
                'timeout' => 10,
                'verify' => true, // Verificar certificados SSL
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'GPDAuthJWT/1.0'
                ]
            ]);

            // Obtener JWK Set desde el endpoint del issuer
            $response = $client->get($jwksUrl);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $jwkSet = json_decode($response->getBody()->getContents(), true);

            if (!isset($jwkSet['keys']) || !is_array($jwkSet['keys'])) {
                return null;
            }

            // Buscar la clave específica por kid (key ID)
            $jwk = null;
            foreach ($jwkSet['keys'] as $key) {

                if (isset($key['kid']) && $key['kid'] === $keyId) {
                    $jwk = $key;
                    break;
                }
            }
            // 4️⃣ Validar algoritmo
            if ($jwk == null || ($jwk['alg'] ?? null) !== $issuer->getAlg()) {
                throw new Exception('Invalid algorithm');
            }
            return $jwk;
        } catch (GuzzleException $e) {
            // Error en la consulta HTTP
            error_log("Error fetching JWKS from {$jwksUrl}: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            // Otro tipo de error
            error_log("Error processing JWKS: " . $e->getMessage());
            return null;
        }
    }

    public function isValidAudience(TrustedIssuer $issuer, string $audience): bool
    {
        $entityManager = $this->context->getEntityManager();
        $qb = $entityManager->createQueryBuilder()->from(TrustedIssuerAudience::class, 'tia')
            ->select('tia')
            ->where('tia.trustedIssuer = :issuer')
            ->andWhere('tia.audience like :audience')
            ->andWhere('tia.status = :status')
            ->setParameter('issuer', $issuer)
            ->setParameter('audience', $audience)
            ->setParameter('status', 'active')
            ->setMaxResults(1);
        $result = $qb->getQuery()->getOneOrNullResult();
        return ($result instanceof TrustedIssuerAudience);
    }

    public function filterAllowedRolesForIssuer(TrustedIssuer $issuer, array $roles): array
    {
        $allowedRoles = [];
        /** @var Role $role */
        foreach ($issuer->getAllowedRoles() as $role) {
            if (in_array($role->getCode(), $roles)) {
                $allowedRoles[] = $role->getCode();
            }
        }
        return $allowedRoles;
    }
}
