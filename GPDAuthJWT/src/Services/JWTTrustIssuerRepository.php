<?php

namespace GPDAuthJWT\Services;

use GPDAuthJWT\Entities\TrustedIssuer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;
use GPDAuth\Entities\Role;
use GPDAuthJWT\Entities\TrustedIssuerAudience;
use GPDAuthJWT\Contracts\JWTTrustIssuerRepositoryInterface;
use GPDAuthJWT\Entities\TrustedIssuerRoleMapping;
use GPDCore\Contracts\AppContextInterface;

class JWTTrustIssuerRepository implements JWTTrustIssuerRepositoryInterface
{


    private array $issuerCache = [];
    private array $audienceCache = [];


    public function __construct(private AppContextInterface $context) {}


    protected function getIssuer(string $issuer): ?TrustedIssuer
    {
        if ($this->issuerCache !== null && isset($this->issuerCache[$issuer])) {
            return $this->issuerCache[$issuer];
        }

        $entityManager = $this->context->getEntityManager();
        $this->issuerCache[$issuer] = $entityManager->createQueryBuilder()->from(TrustedIssuer::class, 'ti')
            ->leftJoin('ti.roleMappings', 'r')
            ->select(['ti', 'r'])
            ->where('ti.issuer = :issuer')
            ->andWhere('ti.status = :status')
            ->setParameter('issuer', $issuer)
            ->setParameter('status', 'active')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $this->issuerCache[$issuer];
    }

    public function isTrustedIssuer(string $iss): bool
    {
        $issuer = $this->getIssuer($iss);
        return ($issuer instanceof TrustedIssuer);
    }

    /** TODO: Agregar cache */
    public function fetchJsonWebKeyByKeyId(string $iss, string $keyId): ?array
    {
        $issuer = $this->getIssuer($iss);
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

    public function getIssuerAlgorithm(string $iss): ?string
    {
        $issuer = $this->getIssuer($iss);
        if (!$issuer) {
            return null;
        }
        return $issuer->getAlg();
    }

    public function isValidAudience(string $iss, string $audience): bool
    {

        if (isset($this->audienceCache[$iss][$audience])) {
            return $this->audienceCache[$iss][$audience];
        }
        $issuer = $this->getIssuer($iss);
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
        $isValid = ($result instanceof TrustedIssuerAudience);
        $this->audienceCache[$iss][$audience] = $isValid;
        return $this->audienceCache[$iss][$audience];
    }

    public function getAllowedRolesForIssuer(string $iss, array $roles): array
    {
        $allowedRoles = [];
        $issuer = $this->getIssuer($iss);
        if (!$issuer) {
            return $allowedRoles;
        }
        /** @var TrustedIssuerRoleMapping $role */
        foreach ($issuer->getRoleMappings() as $role) {
            if (in_array($role->getExternalRoleCode(), $roles)) {
                $allowedRoles[] = $role->getInternalRoleCode();
            }
        }
        return $allowedRoles;
    }
}
