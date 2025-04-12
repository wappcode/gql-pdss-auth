<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;
use GraphQL\Doctrine\Annotation as API;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;





#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_roles")]
#[ORM\Index(name: "role_code_idx", columns: ["code"]),]
#[ORM\Index(name: "role_title_idx", columns: ["title"])]
#[ORM\Index(name: "role_created_idx", columns: ["created"])]
#[ORM\Index(name: "role_updated_idx", columns: ["updated"])]
/**
 * Doctrine Entity for Roles
 * Entidad Doctrine para los Roles de usuarios
 */
class Role extends AbstractEntityModel
{




    #[ORM\Column(type: "string", unique: true)]

    protected string $code;

    #[ORM\Column(type: "string", nullable: false)]

    protected string $title;

    #[ORM\OneToMany(targetEntity: "\GPDAuth\Entities\User", mappedBy: "roles")]
    protected Collection $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new ArrayCollection();
    }

    public function getCode(): string
    {
        return $this->code;
    }


    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }
    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }
    public function getUsers(): Collection
    {
        return $this->users;
    }
}
