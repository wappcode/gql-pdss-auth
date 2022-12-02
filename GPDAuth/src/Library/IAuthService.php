<?php

namespace GPDAuth\Library;

use GPDAuth\Entities\User;

interface IAuthService
{
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
    public function getUser(): ?array;
    public function setUser(array $user): void;
}
