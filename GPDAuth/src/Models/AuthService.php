<?php

namespace GPDAuth\Models;

/**
 * Interface para servicios de autenticación
 * Define los métodos necesarios para manejar la autenticación de usuarios
 * TODO: Cambiar por AuthServiceInterface 
 */
interface AuthService
{
    public function login(string $username, string $password, string $grantType);
    public function logout(): void;
    public function isSigned(): bool;
    public function hasRole(string $role): bool;
    public function hasSomeRoles(array $roles): bool;
    public function hasAllRoles(array $roles): bool;
    public function hasPermission(string $resource, string $permission, ?string $scope = null): bool;
    public function hasSomePermissions(array $resources, array $permission, ?array $scopes = null): bool;
    public function hasAllPermissions(array $resources, array $permission, ?array $scopes = null): bool;
    public function getAuthenticatedUser(): ?AuthenticatedUser;
}
