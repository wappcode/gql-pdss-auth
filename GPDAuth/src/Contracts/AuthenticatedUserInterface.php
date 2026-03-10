<?php

namespace GPDAuth\Contracts;

use GPDAuth\Models\AuthenticatedUserType;

interface AuthenticatedUserInterface
{
    public function toArray(): array;
    public function getId(): string;
    public function setId(string $id): self;
    public function getFullName(): string;
    public function setFullName(string $fullName): self;
    public function getFirstName(): ?string;
    public function setFirstName(?string $firstName): self;
    public function getUsername(): string;
    public function setUsername(string $username): self;
    public function getLastName(): ?string;
    public function setLastName(?string $lastName): self;
    public function getEmail(): ?string;
    public function setEmail(?string $email): self;
    public function getPicture(): ?string;
    public function setPicture(?string $picture): self;
    /**
     *
     * @return array<string>
     */
    public function getRoles(): array;
    /**
     *
     * @param array<string> $roles
     * @return self
     */
    public function setRoles(array $roles): self;
    /**
     * Get user permissions
     *
     * @return  array<\GPDAuth\Models\ResourcePermission>
     */
    public function getPermissions(): array;
    /**
     * Set user permissions
     *
     * @param  array<\GPDAuth\Models\ResourcePermission>  $permissions  User permissions
     *
     * @return  self
     */
    public function setPermissions(array $permissions): self;
    public function getActive(): bool;
    public function setActive(bool $active): self;
    public function getType(): AuthenticatedUserType;
    public function setType(AuthenticatedUserType $type): self;
    public function hasRole(string $role): bool;
    /**
     *
     * @param array<string> $roles
     * @return boolean
     */
    public function hasAnyRole(array $roles): bool;
    /**
     *
     * @param array<string> $roles
     * @return boolean
     */
    public function hasAllRoles(array $roles): bool;

    public function hasPermission(string $resource, string $permission, ?string $scope = null): bool;
    /**
     *
     * @param array<string> $resources
     * @param array<string> $permission
     * @param array<string>|null $scopes
     * @return boolean
     */
    public function hasAnyPermission(array $resources, array $permission, ?array $scopes = null): bool;
    /**
     *
     * @param array<string> $resources
     * @param array<string> $permission
     * @param array<string>|null $scopes
     * @return boolean
     */
    public function hasAllPermissions(array $resources, array $permission, ?array $scopes = null): bool;
}
