<?php

namespace GPDAuthJWT\Controllers;

use Firebase\JWT\JWT;
use GPDAuthJWT\Entities\ApiConsumer;
use GPDAuthJWT\Entities\ApiPermission;
use GPDAuthJWT\Entities\JWTKey;
use GPDCore\Library\AbstractAppController;

class AuthTokenController extends AbstractAppController
{

    public function dispatch()
    {
        // OAuth usa application/x-www-form-urlencoded
        $grantType = $_POST['grant_type'] ?? null;
        $clientId  = $_POST['client_id'] ?? null;
        $secret    = $_POST['client_secret'] ?? null;
        $scopeReq  = $_POST['scope'] ?? '';
        if ($grantType !== 'client_credentials') {
            http_response_code(400);
            echo json_encode(['error' => 'unsupported_grant_type']);
            exit;
        }

        if (!$clientId || !$secret) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_client']);
            exit;
        }
        $this->validateClient($clientId, $secret);
        $scopes = $this->getAllowedScopes($clientId, $scopeReq);
        $key = $this->getActiveKey();
        $config = $this->context->getConfig()->get("idp_jwt");
        $iss = $config['issuer'];
        $aud = $config['audience'];
        $expiration = $config['lifetime_seconds'] ?? 3600;
        $jwt = $this->createJWT($clientId, $iss, $aud, $expiration, $scopes, $key);
        echo json_encode([
            'access_token' => $jwt,
            'token_type'   => 'Bearer',
            'expires_in'   => $expiration,
            'scope'        => implode(' ', $scopes),
        ]);
    }

    private function validateClient($clientId, $secret)
    {
        $entityManager = $this->context->getEntityManager();
        $client = $this->$entityManager->createQueryBuilder()->from(ApiConsumer::class, 'c')
            ->where('c.identifier = :identifier')
            ->setParameter('identifier', $clientId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        if (!($client instanceof ApiConsumer) || $client->getStatus() !== 'active') {
            http_response_code(401);
            echo json_encode(['error' => 'invalid_client']);
            exit;
        }
        $secretHash = $client->getSecretHash();
        if (!password_verify($secret, $secretHash)) {
            http_response_code(401);
            echo json_encode(['error' => 'invalid_client']);
            exit;
        }
    }

    private function getAllowedScopes(ApiConsumer $client, string $scopeReq): array
    {
        $entityManager = $this->context->getEntityManager();
        $requestedScopes = array_filter(explode(' ', $scopeReq));

        $qb = $entityManager->createQueryBuilder()->from(ApiPermission::class, 'p')
            ->innerJoin('p.resource', 'r')
            ->innerJoin('p.consumers', 'c')
            ->select(['r.code', 'p.value'])

            ->where('c.id = :consumerId')
            ->setParameter('consumerId', $client->getId());

        $allowedScopes = array_map(function ($row) {
            return $row['code'] . ':' . $row['value'];
        }, $qb->getQuery()->getArrayResult());
        $finalScopes = array_intersect($requestedScopes, $allowedScopes);

        if (empty($finalScopes)) {
            http_response_code(403);
            echo json_encode(['error' => 'insufficient_scope']);
            exit;
        }
        return $requestedScopes;
    }

    private function getActiveKey(): JWTKey
    {
        $entityManager = $this->context->getEntityManager();
        $qb = $entityManager->createQueryBuilder()
            ->from(JWTKey::class, 'k')
            ->andWhere('k.active = :active')
            ->setParameter('active', true)
            ->orderBy('k.creeated', 'DESC')
            ->setMaxResults(1);
        $key = $qb->getQuery()->getOneOrNullResult();

        if (!$key) {
            http_response_code(500);
            echo json_encode(['error' => 'key_not_configured']);
            exit;
        }
        return $key;
    }
    /**
     * Undocumented function
     *
     * @param ApiConsumer $client
     * @param string $iss Quien emite el token (usualmente la URL del auth server)
     * @param string $aud A quien va dirigido el token (usualmente la URL del API)
     * @param array $finalScopes
     * @param JWTKey $key
     * @return void
     */
    private function createJWT(ApiConsumer $client, string $iss, string $aud, int $expiration, array $finalScopes, JWTKey $key)
    {
        $now = time();
        $exp = $now + $expiration; // una hora de validez

        $payload = [
            'iss'   => $iss,
            'sub'   => $client->getIdentifier(),
            'aud'   => $aud,
            'iat'   => $now,
            'exp'   => $exp,
            'jti'   => bin2hex(random_bytes(16)),
            'gty'   => 'client-credentials',
            'scope' => implode(' ', $finalScopes),
            'azp'   => $client->getIdentifier(),
        ];

        $jwt = JWT::encode(
            $payload,
            $key->getPrivateKey(),
            'RS256',
            $key->getKid()
        );
        return $jwt;
    }
}
