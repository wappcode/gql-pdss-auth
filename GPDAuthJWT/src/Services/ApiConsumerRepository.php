<?php


namespace GPDAuthJWT\Services;

use Doctrine\ORM\EntityManager;
use GPDAuth\Enums\PermissionAccess;
use GPDAuth\Enums\PermissionValue;
use GPDAuthJWT\Entities\ApiConsumer;
use GPDAuthJWT\Contracts\ApiConsumerRepositoryInterface;
use GPDAuthJWT\Entities\ApiConsumerPermission;
use GPDAuthJWT\Entities\ApiConsumerRoleMapping;
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
        $qb = $this->entityManager->createQueryBuilder()->from(ApiConsumer::class, 'ac')
            ->leftJoin('ac.permissions', 'p')
            ->leftJoin('ac.roleMappings', 'r')
            ->select(['ac', 'p', 'r'])
            ->where('ac.identifier = :identifier')
            ->andWhere('ac.status = :status')
            ->setParameter('identifier', $identifier)
            ->setParameter('status', 'active')
            ->setMaxResults(1);
        /** @var ApiConsumer | null */
        $consumer = $qb->getQuery()->getOneOrNullResult();
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
            $accessFormated = strtolower($permission->getAccess());
            if ($accessFormated === strtolower(PermissionAccess::DENY->value)) {
                continue;
            }

            $resource = strtolower($permission->getResource());

            $consumerPermission = $consumerPermissions->filter(function ($perm) use ($resource) {
                return strtolower($perm->getResourceCode()) === $resource;
            });

            if (!($consumerPermission instanceof ApiConsumerPermission)) {
                continue; // No tiene permiso para este recurso
            }
            $consumerPermission = strtolower($permission->getValue());
            $permissionValue = strtolower($permission->getValue());
            if ($consumerPermission === strtolower(PermissionValue::ALL->value) || $consumerPermission === $permissionValue) {
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

    public function getAllowedRolesForIssuer(string $consumerId, array $roles): array
    {
        $allowedRoles = [];
        $consumer = $this->getConsumer($consumerId);
        if (!$consumer) {
            return $allowedRoles;
        }
        /** @var ApiConsumerRoleMapping $role */
        foreach ($consumer->getRoleMappings() as $role) {
            if (in_array($role->getExternalRoleCode(), $roles)) {
                $allowedRoles[] = $role->getInternalRoleCode();
            }
        }
        return $allowedRoles;
    }
}
