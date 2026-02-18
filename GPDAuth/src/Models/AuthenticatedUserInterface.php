<?php

namespace GPDAuth\Models;

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
    public function getRoles();
    public function setRoles(array $roles);
    public function getPermissions();
    public function setPermissions(array $permissions);
    public function getActive();
    public function setActive($active);
    public function getType(): AuthenticatedUserType;
    public function setType(AuthenticatedUserType $type): self;
    public function hasRole(string $role): bool;
    public function hasSomeRoles(array $roles): bool;
    public function hasAllRoles(array $roles): bool;
    public function hasPermission(string $resource, string $permission, ?string $scope = null): bool;
    public function hasSomePermissions(array $resources, array $permission, ?array $scopes = null): bool;
    public function hasAllPermissions(array $resources, array $permission, ?array $scopes = null): bool;
}
