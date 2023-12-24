<?php

namespace GPDAuth\Library;

use GPDAuth\Models\AuthSession;

interface IAuthService
{
    const AUTHENTICATION_METHOD_SESSION = 'SESSION';
    const AUTHENTICATION_METHOD_JWT = 'JWT';
    const AUTHENTICATION_METHOD_JWT_OR_SESSION = 'JWT_OR_SESSION';
    const AUTHENTICATION_METHOD_SESSION_OR_JWT = 'SESSION_OR_JWT';

    public function login(string $username, string $password);
    public function logout(): void;
    public function isSigned(): bool;
    public function hasRole(string $role): bool;
    public function hasSomeRoles(array $roles): bool;
    public function hasAllRoles(array $roles): bool;
    public function hasPermission(string $resource, string $permission, ?string $scope = null): bool;
    public function hasSomePermissions(array $resources, array $permission, ?array $scopes = null): bool;
    public function hasAllPermissions(array $resources, array $permission, ?array $scopes = null): bool;
    public function getRoles(): array;
    public function getSession(): ?AuthSession;
    public function getAuthId(): ?string;
    public function getNewJWT(): ?string;
}
