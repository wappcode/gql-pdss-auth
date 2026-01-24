<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDAuth\Entities\Resource;
use GPDCore\Entities\AbstractEntityModel;



#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_permissions")]
class Permission extends AbstractEntityModel
{
    #[ORM\ManyToOne(targetEntity: "\GPDAuth\Entities\Resource")]
    #[ORM\JoinColumn(name: "resource_id", referencedColumnName: "id", nullable: false)]

    protected Resource $resource;

    #[ORM\ManyToOne(targetEntity: "\GPDAuth\Entities\User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]

    protected ?User $user;

    #[ORM\ManyToOne(targetEntity: "\GPDAuth\Entities\Role")]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName: "id", nullable: true)]

    protected ?Role $role;

    #ipo de acceso ALLOW | DENY
    #[ORM\Column(type: "string", name: "permission_access", nullable: false, length: 255)]
    protected $access;

    /**
     * Solo se asigna un permiso a la vez.
     *  Si se requieren otros permisos se debe crear un registro para cada uno o utilizar Permission ALL para que aplique a todos
     * @var string
     */
    #[ORM\Column(type: "string", length: 255, nullable: false, name: "permision_value")]
    protected string $value;

    /** 
     * Solo se asigna un scope a la vez
     * Si se requieren diferentes scopes debe haber un registro para cada scope o utlizar SCOPE ALL para que se asigne a todos los scopes
     * 
     * @var string
     */
    #[ORM\Column(type: "string", nullable: true)]
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


    public function getAccess(): string
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
