<?php

namespace GPDAuth\Services;

use Doctrine\ORM\EntityManager;
use GPDAuth\Entities\Permission;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDAuth\Library\PasswordManager;
use GPDAuth\Models\AuthenticatedUserInterface;
use GPDAuth\Models\ResourcePermission;
use GPDAuth\Models\UserRepositoryInterface;

/**
 * Implementación concreta del repositorio de usuarios usando Doctrine
 */
class UserRepository implements UserRepositoryInterface
{
    private EntityManager $entityManager;
    private array $userCache = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public  function findById(string $userId): ?AuthenticatedUserInterface
    {
        $authUser = $this->userCache[$userId] ?? null;
        if ($authUser === null) {
            $repository = $this->entityManager->find(User::class, $userId);
            $user = $repository->find($userId);
            if ($user instanceof User) {
                $authUser = $this->crateAuthenticatedUserInterface($user);
                $this->userCache[$userId] = $authUser;
            }
        }
        return $this->userCache[$userId];
    }
    public function validateCredentials(string $username, string $password): ?AuthenticatedUserInterface
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $username]);

        if (!($user instanceof User) || !$user->getActive()) {
            return null;
        }
        $encodedPassword = PasswordManager::encode($password, $user->getSalt());
        if ($user->getPassword() === $encodedPassword) {
            $authUser = $this->crateAuthenticatedUserInterface($user);
            $this->userCache[$authUser->getId()] = $authUser;
            return $authUser;
        }

        return null;
    }

    public function updateLastAccess(AuthenticatedUserInterface $user): void
    {

        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(User::class, 'u')
            ->set('u.lastLogin', ':lastLogin')
            ->where('u.id = :userId')
            ->setParameter('lastLogin', new \DateTime());
        $qb->setParameter('userId', $user->getId());
        $qb->getQuery()->execute();
    }


    /**
     * Crear un registro AuthenticatedUserInterface desde un registro de usuario de la base de datos
     * @param User $user
     * @return AuthenticatedUserInterface
     */
    private function crateAuthenticatedUserInterface(User $user): AuthenticatedUserInterface
    {
        $authUser = new AuthenticatedUserInterface();
        $authUser->setId($user->getId());
        $authUser->setUsername($user->getUsername());
        $authUser->setFullName($user->getFullName());
        $authUser->setFirstName($user->getFirstName());
        $authUser->setLastName($user->getLastName());
        $authUser->setEmail($user->getEmail());
        $authUser->setPicture($user->getPicture());
        $roles = $this->getRolesFromDB($authUser);
        $permissions = $this->getPermissionsFromDB($roles, $authUser->getId());
        $authUser->setRoles($roles);
        $authUser->setPermissions($permissions);
        return $authUser;
    }
    private function getRolesFromDB(AuthenticatedUserInterface $user): array
    {
        $qb = $this->entityManager->createQueryBuilder()->from(User::class, 'u')
            ->innerJoin('u.roles', 'r')
            ->select('r.code')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId());
        $roles = $qb->getQuery()->getArrayResult();
        return array_map(fn($role) => $role['code'], $roles);
    }


    /**
     * Gets the user permissions from database
     * Establece los permisos del usuario obtenidos desde los registros de la base de datos ordenados por prioridad
     * @param array $rolesCodes [string]
     * @param ?string $userId 
     * @return array  Permission as array
     */
    protected function getPermissionsFromDB(array $rolesCodes, ?string $userId = null): array
    {

        $qb = $this->entityManager->createQueryBuilder()->from(Permission::class, 'permission')
            ->innerJoin('permission.resource', 'resource')
            ->leftJoin('permission.user', 'user')
            ->leftJoin('permission.role', 'role')
            ->select(['permission', 'partial user.{id}', 'partial role.{id, code}', 'partial resource.{id,code}']);
        $condigionRole = $qb->expr()->in('role.code', ':rolesCodes');
        $conditionUser = 'user.id like :userId';
        $conditionGlobal = $qb->expr()->andX($qb->expr()->isNull("permission.user"), $qb->expr()->isNull("permission.role"));

        if ($userId != null) {
            $qb->andWhere($qb->expr()->orX($conditionUser, $condigionRole, $conditionGlobal))
                ->setParameter(':userId', $userId);
        } else {
            $qb->andWhere($qb->expr()->orX($condigionRole, $conditionGlobal));
        }
        $qb->setParameter(':rolesCodes', $rolesCodes)
            ->orderBy('permission.updated', 'desc');
        $permissions = $qb->getQuery()->getResult() ?? [];
        $permissions = $this->sortDBPermissions($permissions);
        $permissionsResult = array_map(function (Permission $permissionObj) {
            $resource = $permissionObj->getResource()->getCode();
            $access = $permissionObj->getAccess();
            $value = $permissionObj->getValue();
            $scope = $permissionObj->getScope();
            $permission = new ResourcePermission($resource, $access, $value, $scope);
            return $permission;
        }, $permissions);

        return $permissionsResult;
    }

    /**
     * Ordena los permisos que provienen de la base de datos. Da prioridad a usuario, roles y al final permisos globales
     * el orden es descendiente por fecha de actualización
     */
    protected function sortDBPermissions(array $permissions): array
    {

        usort($permissions, function (Permission $a, Permission $b) {
            // ordena primero los permisos que son de usuario
            $userA = ($a->getUser() instanceof User) ? -1 : 1;
            $userB = ($b->getUser() instanceof User) ? -1 : 1;
            if ($userA != $userB) {
                return $userA <=> $userB;
            }
            // ordena segundo los permisos que son de roles
            $roleA = ($a->getRole() instanceof Role) ? -1 : 1;
            $roleB = ($b->getRole() instanceof Role) ? -1 : 1;
            if ($roleA != $roleB) {
                return $roleA <=> $roleB;
            }

            // ordena al final los permisos por fecha en forma descendente para darle importancia a los últimos

            $updatedA = $a->getUpdated();
            $updatedB = $b->getUpdated();
            $timeA = $updatedA->getTimestamp();
            $timeB = $updatedB->getTimestamp();
            return $timeB <=> $timeA; // orden descendente por fecha

        });
        return $permissions;
    }
}
