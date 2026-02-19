<?php

namespace GPDAuth\Models;



abstract class AbstractAuthenticatedUser implements AuthenticatedUserInterface
{

    /**
     * Tipo de usuario api_client, local_user, extern_user
     *
     * @var AuthenticatedUserType
     */
    private AuthenticatedUserType $type;
    /**
     * User ID
     *
     * @var string
     */
    private string $id;

    /**
     * User full name
     *
     * @var string
     */
    private string $fullName;

    /**
     * User first name
     *
     * @var ?string
     */
    private ?string $firstName;

    /**
     * Username
     *
     * @var string
     */
    private string $username;

    /**
     * User last name
     *
     * @var ?string
     */
    private ?string $lastName;

    /**
     * User email
     *
     * @var ?string
     */
    private ?string $email;

    /**
     * User profile picture URL
     *
     * @var ?string
     */
    private ?string $picture;

    /**
     * User permissions
     *
     * @var array
     */
    private array $permissions = [];

    /**
     * User roles
     *
     * @var array
     */
    private array $roles = [];


    private bool $active = true;

    public function toArray(): array
    {
        $data = get_object_vars($this);
        // Convertir el enum a su valor string
        if (isset($data['type']) && $data['type'] instanceof AuthenticatedUserType) {
            $data['type'] = $data['type']->value;
        }
        $permissions = array_map(function (ResourcePermission $permission) {
            return $permission->toArray();
        }, $this->getPermissions());
        $data['permissions'] = $permissions;
        return $data;
    }


    /**
     * Get user ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set user ID
     *
     * @param string $id User ID
     *
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get user full name
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * Set user full name
     *
     * @param string $fullName User full name
     *
     * @return self
     */
    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * Get user first name
     *
     * @return ?string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Set user first name
     *
     * @param ?string $firstName User first name
     *
     * @return self
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username Username
     *
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get user last name
     *
     * @return ?string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Set user last name
     *
     * @param ?string $lastName User last name
     *
     * @return self
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get user email
     *
     * @return ?string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set user email
     *
     * @param ?string $email User email
     *
     * @return self
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get user profile picture URL
     *
     * @return ?string
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    /**
     * Set user profile picture URL
     *
     * @param ?string $picture User profile picture URL
     *
     * @return self
     */
    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * Get user roles
     *
     * @return  array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set user roles
     *
     * @param  array  $roles  User roles
     *
     * @return  self
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get user permissions
     *
     * @return  array [ResourcePermission]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set user permissions
     *
     * @param  array  $permissions  [ResourcePermission]
     *
     * @return  self
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Get the value of active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @return  self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of type
     */
    public function getType(): AuthenticatedUserType
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */
    public function setType(AuthenticatedUserType $type): self
    {
        $this->type = $type;

        return $this;
    }

    abstract public function hasRole(string $role): bool;
    abstract public function hasAnyRole(array $roles): bool;
    abstract public function hasAllRoles(array $roles): bool;
    abstract public function hasPermission(string $resource, string $permission, ?string $scope = null): bool;
    abstract public function hasAnyPermission(array $resources, array $permission, ?array $scopes = null): bool;
    abstract public function hasAllPermissions(array $resources, array $permission, ?array $scopes = null): bool;
}
