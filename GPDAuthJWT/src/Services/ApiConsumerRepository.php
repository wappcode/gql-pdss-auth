<?php


namespace GPDAuthJWT\Services;

use Doctrine\ORM\EntityManager;
use GPDAuth\Entities\PermissionAccess;
use GPDAuth\Entities\PermissionValue;
use GPDAuthJWT\Entities\ApiConsumer;
use GPDAuthJWT\Contracts\ApiConsumerRepositoryInterface;
use GPDAuthJWT\Entities\ApiPermission;
use GPDAuthJWT\Library\JwtUtilities;

class ApiConsumerRepository implements ApiConsumerRepositoryInterface
{

    private EntityManager $entityManager;
    private array $trustedConsumersCache = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function isTrustedConsumer(string $consumerId): bool
    {
        if (!isset($this->trustedConsumersCache[$consumerId])) {
            $consumer = $this->getConsumer($consumerId);
            $this->trustedConsumersCache[$consumerId] = $consumer;
        }
        return ($this->trustedConsumersCache[$consumerId] instanceof ApiConsumer);
    }


    public function getConsumer(string $identifier): ?ApiConsumer
    {
        if (isset($this->trustedConsumersCache[$identifier])) {
            return $this->trustedConsumersCache[$identifier];
        }
        /** @var ApiConsumer | null */
        $consumer = $this->entityManager->getRepository(ApiConsumer::class)->findOneBy(['identifier' => $identifier]);
        if ($consumer && $consumer->isActive()) {
            $this->trustedConsumersCache[$identifier] = $consumer;
        }
        return $consumer;
    }
    public function getValidPermissionsForConsumer(string $consumerId, array $decoded): array
    {
        $consumer = $this->getConsumer($consumerId);
        if (!$consumer) {
            return [];
        }

        $permissions = JwtUtilities::convertScopesToPermissions($decoded);
        $validPermissions = [];
        $consumerPermissions = $consumer->getPermissions();
        /** @var ResourcePermission */
        foreach ($permissions as $permission) {

            if ($permission->getAccess() === PermissionAccess::DENY) {
                continue;
            }
            $resource = $permission->getResource();

            $consumerPermission = $consumerPermissions->filter(function ($perm) use ($resource) {
                return $perm->getResource()->getCode() === $resource;
            });

            if (!($consumerPermission instanceof ApiPermission)) {
                continue; // No tiene permiso para este recurso
            }
            $consumerPermission = strtolower($permission->getValue());
            $permissionValue = strtolower($permission->getValue());
            if ($consumerPermission === PermissionValue::ALL || $consumerPermission === $permissionValue) {
                $validPermissions[] = $permission;
            }
        }
        return $validPermissions;
    }

    public function getConsumerName(string $consumerId): string
    {
        $consumer = $this->getConsumer($consumerId);
        return $consumer ? $consumer->getName() : '';
    }
    public function getConsumerIdFromJwtPayload(array $payload): ?string
    {
        $clientId = $payload['azp'] ?? $payload['client_id'] ?? null;
        return $clientId;
    }
    public function isM2mToken(array $payload): bool
    {
        $isM2M =
            ($payload['gty'] ?? null) === 'client-credentials'
            || isset($payload['client_id'])
            || (isset($payload['azp']) && $payload['sub'] === $payload['azp']);
        return $isM2M;
    }
}
