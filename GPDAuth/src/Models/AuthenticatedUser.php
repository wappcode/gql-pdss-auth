<?php

namespace GPDAuth\Models;

use GPDAuth\Contracts\AuthenticatedUserInterface;

use GPDAuth\Enums\PermissionAccess;
use GPDAuth\Enums\PermissionValue;

class AuthenticatedUser extends AbstractAuthenticatedUser implements AuthenticatedUserInterface
{

    public function hasRole(string $role): bool
    {
        $roles = $this->getRoles() ?? [];
        return in_array($role, $roles);
    }
    public function hasAnyRole(array $roles): bool
    {
        $userRoles = $this->getRoles() ?? [];
        $intersect = array_intersect($userRoles, $roles);
        return count($intersect) > 0;
    }
    public function hasAllRoles(array $roles): bool
    {
        $userRoles = $this->getRoles() ?? [];
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
     * No se consideran mayusculas o minusculas en la comparación de los valores y access de los permisos
     * 
     * @param string $resource
     * @param string $permissionValue
     * @param string|null $scope
     * @return boolean
     */
    public function hasPermission(string $resource, string $permissionValue, ?string $scope = null): bool
    {
        $permission = $this->findPermission($resource, $permissionValue);
        $scopeFormated = is_string($scope) ? strtolower($scope) : null;
        if (!($permission instanceof ResourcePermission)) {
            return false;
        }
        $permissionScopeFormated = $permission->getScope() != null ? strtolower($permission->getScope()) : null;
        if (!empty($scopeFormated) && $scopeFormated != $permissionScopeFormated) {
            return false;
        }
        return strtolower($permission->getAccess()) === strtolower(PermissionAccess::ALLOW->value);
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
    public function hasAnyPermission(array $resources, array $permissionsValues, ?array $scopes = null): bool
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
     * Los valores, resources y access de los permisos se comparan sin considerar mayusculas o minusculas
     * @param string $resource
     * @param string $permissionValue
     * @return ResourcePermission|null
     */
    private function findPermission(string $resource, string $permissionValue): ?ResourcePermission
    {
        $result = null;
        $permissions = $this->getPermissions() ?? [];
        $permissionValueFormated = strtolower($permissionValue);
        $resourceFormated = strtolower($resource);
        /** @var ResourcePermission */
        foreach ($permissions as $permission) {
            $permisionVF = strtolower($permission->getValue());
            $resourceVF = strtolower($permission->getResource());
            if (
                $resourceFormated != $resourceVF ||
                ($permissionValueFormated != $permisionVF &&
                    $permisionVF != strtolower(PermissionValue::ALL->value)
                )
            ) continue;
            if (strtolower($permission->getAccess()) == strtolower(PermissionAccess::ALLOW->value)) {
                return $permission;
            } else {
                return null;
            }
        }
        return $result;
    }
}
