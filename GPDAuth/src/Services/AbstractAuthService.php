<?php

namespace GPDAuth\Services;

use Exception;
use GPDAuth\Entities\PermissionAccess;
use GPDAuth\Entities\PermissionValue;
use GPDAuth\Models\ResourcePermission;
use GPDAuth\Models\AuthenticatedUser;
use GPDAuth\Models\AuthService;

@session_start();
abstract class AbstractAuthService implements AuthService
{


    protected ?AuthenticatedUser $authenticatedUser = null;

    /**
     *
     * 
     * Hay que inicializar sesion, roles y permisos
     * 
     * @param string $identifier (username o client_id)
     * @param string $password
     * @throws Exception
     */
    public abstract function login(string $identifier, string $password, string $grantType): void;

    public abstract function logout(): void;

    public function getAuthenticatedUser(): ?AuthenticatedUser
    {
        return $this->authenticatedUser;
    }
    /**
     * Se considera que esta firmado si tiene registro de usuario
     *
     * @return boolean
     */
    public function isSigned(): bool
    {
        return $this->authenticatedUser instanceof AuthenticatedUser;
    }
    public function hasRole(string $role): bool
    {
        $roles = $this->authenticatedUser?->getRoles() ?? [];
        return in_array($role, $roles);
    }
    public function hasSomeRoles(array $roles): bool
    {
        $userRoles = $this->authenticatedUser?->getRoles() ?? [];
        $intersect = array_intersect($userRoles, $roles);
        return count($intersect) > 0;
    }
    public function hasAllRoles(array $roles): bool
    {
        $userRoles = $this->authenticatedUser?->getRoles() ?? [];
        $intersect = array_intersect($userRoles, $roles);
        $intersectUnique = array_unique($intersect);
        return count($intersect) == count($intersectUnique);
        return true;
    }

    /**
     * 
     * Determina si el usuario tiene permiso para un determinado recurso
     * Solo se consideran permisos con acceso autorizado
     * Sobreescribir este método para un servicio personalizado
     * 
     * @param string $resource
     * @param string $permissionValue
     * @param string|null $scope
     * @return boolean
     */
    public function hasPermission(string $resource, string $permissionValue, ?string $scope = null): bool
    {
        $permission = $this->findPermission($resource, $permissionValue, $scope);
        if (!($permission instanceof ResourcePermission)) {
            return false;
        }
        if (!empty($scope) && $scope != $permission->getScope()) {
            return false;
        }
        return $permission->getAccess() === PermissionAccess::ALLOW;
    }
    /**
     * Determina si el usuario tiene algun permiso para alguno de los recursos
     * Solo se consideran permisos con acceso autorizado
     * Sobreescribir  método hasPermission para un servicio personalizado
     *
     * @param array $resources
     * @param array $permissionsValues
     * @param array|null $scopes
     * @return boolean
     */
    public function hasSomePermissions(array $resources, array $permissionsValues, ?array $scopes = null): bool
    {
        $result = false;
        foreach ($resources as $resource) {
            foreach ($permissionsValues as $permissionValue) {
                if (empty($scopes)) {
                    $flag = $this->hasPermission($resource, $permissionValue);
                    if ($flag === true) {
                        $result = true;
                        break 2;
                    }
                    continue;
                }
                foreach ($scopes as $scope) {
                    $flag = $this->hasPermission($resource, $permissionValue, $scope);
                    if ($flag === true) {
                        $result = true;
                        break 3;
                    }
                }
            }
        }
        return $result;
    }
    /**
     * Determina si el usuario tiene todos los permisos para todos los recursos
     * Solo se consideran permisos con acceso autorizado
     * Sobreescribir  método hasPermission para un servicio personalizado
     * @param array $resources
     * @param array $permissionsValues
     * @param array|null $scopes
     * @return boolean
     */
    public function hasAllPermissions(array $resources, array $permissionsValues, ?array $scopes = null): bool
    {
        if (empty($resources) || empty($permissionsValues)) {
            return false;
        }
        $result = true;
        foreach ($resources as $resource) {
            foreach ($permissionsValues as $permissionValue) {
                if (empty($scopes)) {
                    $flag = $this->hasPermission($resource, $permissionValue);
                    if ($flag === false) {
                        $result = false;
                        break 2;
                    }
                    continue;
                }
                foreach ($scopes as $scope) {
                    $flag = $this->hasPermission($resource, $permissionValue, $scope);
                    if ($flag === false) {
                        $result = false;
                        break 3;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Localiza un determinado permiso con acceso autorizado
     * Los permisos con acceso denegado retornan null
     *
     * @param string $resource
     * @param string $permissionValue
     * @return ResourcePermission|null
     */
    private function findPermission(string $resource, string $permissionValue): ?ResourcePermission
    {
        $result = null;
        $permissions = $this->authenticatedUser?->getPermissions() ?? [];
        /** @var ResourcePermission */
        foreach ($permissions as $permission) {
            if ($resource != $permission->getResource() || ($permissionValue != $permission->getValue() && $permission->getValue() != PermissionValue::ALL)) continue;
            if ($permission->getAccess() == PermissionAccess::ALLOW) {
                return $permission;
            } else {
                return null;
            }
        }
        return $result;
    }
}
