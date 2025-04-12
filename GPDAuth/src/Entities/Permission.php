<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDAuth\Entities\Resource;
use GPDCore\Entities\AbstractEntityModel;
use GraphQL\Doctrine\Annotation as API;



#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_permissions")]
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

    #[ORM\ManyToOne(targetEntity: "\GPDAuth\Entities\Resource")]
    #[ORM\JoinColumn(name: "resource_id", referencedColumnName: "id", nullable: false)]

    protected $resource;

    #[ORM\ManyToOne(targetEntity: "\GPDAuth\Entities\User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]

    protected $user;

    #[ORM\ManyToOne(targetEntity: "\GPDAuth\Entities\Role")]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName: "id", nullable: true)]

    protected $role;

    #ipo de acceso ALLOW | DENY
    #[ORM\Column(type: "string", name: "permission_access", nullable: false, length: 255)]

    protected $access;

    #olo se asigna un permiso a la vez.
    #i se requieren otros permisos se debe crear un registro para cada uno o utilizar Permission ALL para que aplique a todos
    #var string

    protected $value;

    #olo se asigna un scope a la vez
    #i se requieren diferentes scopes debe haber un registro para cada scope o utlizar SCOPE ALL para que se asigne a todos los scopes
    #var string

    protected $scope;



    public function getResource(): Resource
    {
        return $this->resource;
    }


    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;

        return $this;
    }


    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role)
    {
        $this->role = $role;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }


    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }


    public function getAccess()
    {
        return $this->access;
    }


    public function setAccess(string $access)
    {
        $this->access = $access;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }


    public function setScope(string $scope)
    {
        $this->scope = $scope;

        return $this;
    }
}
