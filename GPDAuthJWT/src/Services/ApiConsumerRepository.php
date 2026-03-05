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

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findByIdentifier(string $identifier): ?ApiConsumer
    {
        /** @var ApiConsumer | null */
        $consumer = $this->entityManager->getRepository(ApiConsumer::class)->findOneBy(['identifier' => $identifier]);
        return $consumer;
    }
    public function getAllowedPermissions(ApiConsumer $consumer, array $decoded): array
    {

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
}
