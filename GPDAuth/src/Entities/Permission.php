<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDAuth\Entities\Resource;
use GPDCore\Entities\AbstractEntityModel;
use GraphQL\Doctrine\Annotation as API;



/**
 * @ORM\Entity()
 * @ORM\Table(name="gpd_auth_permissions")
 */
class Permission extends AbstractEntityModel
{

    const RELATIONS_MANY_TO_ONE = ['resource', 'user', 'role'];

    const ALLOW = "ALLOW";
    const DENY = "DENY";
    const ALL = "ALL";
    const VIEW = "VIEW";
    const CREATE = "CREATE";
    const UPDATE = "UPDATE";
    const DELETE = "DELETE";
    const UPLOAD = "UPLOAD";
    const DOWNLOAD = "DOWNLOAD";

    /**
     * @ORM\ManyToOne(targetEntity="\GPDAuth\Entities\Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=false)
     * @var \GPDAuth\Entities\Resource
     */
    protected $resource;

    /**
     * @ORM\ManyToOne(targetEntity="\GPDAuth\Entities\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * @var \GPDAuth\Entities\User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\GPDAuth\Entities\Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=true)
     * @var \GPDAuth\Entities\Role
     */
    protected $role;

    /**
     * @ORM\Column(type="string", name="permission_access", nullable=false, length=255)
     * @var string
     */
    protected $access;

    /**
     * @ORM\Column(type="string", name="permision_value", nullable=false, length=255)
     * @var string
     */
    protected $value;

    /**
     * Los valores se guardan como cadenas separadas por coma
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $scope;



    /**
     * Get the value of resource
     *
     * @return  \GPDAuth\Entities\Resource
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Set the value of resource
     *
     * @param  \GPDAuth\Entities\Resource  $resource
     *
     * @return  self
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get the value of user
     *
     * @return  ?\GPDAuth\Entities\User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @param  ?\GPDAuth\Entities\User  $user
     *
     * @return  self
     */
    public function setUser(?User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of role
     *
     * @return  ?\GPDAuth\Entities\Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @param  ?\GPDAuth\Entities\Role  $role
     *
     * @return  self
     */
    public function setRole(?Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return  string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @API\Input(type="GPDAuth\Graphql\TypePermissionValue")
     * 
     * @param  string  $value
     *
     * @return  self
     */
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of access
     *
     * @return  string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set the value of access
     *
     * @API\Input(type="GPDAuth\Graphql\TypePermissionAccess")
     * 
     * @param  string  $access
     *
     * @return  self
     */
    public function setAccess(string $access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get the value of scope
     * @API\Field(type="?string", description="keys separados por comas")
     * @return  string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * Set the value of scope
     * @API\Input(type="?string", description="keys separados por comas")
     * @param  string  $scope
     *
     * @return  self
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;

        return $this;
    }
}
