<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;
use GraphQL\Doctrine\Annotation as API;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;



/**
 * Doctrine Entity for Roles
 * Entidad Doctrine para los Roles de usuarios
 * 
 * @ORM\Entity()
 * @ORM\Table(name="gpd_auth_role", indexes={
 * @ORM\Index(name="role_code_idx",columns={"code"}),
 * @ORM\Index(name="role_title_idx",columns={"title"}),
 * @ORM\Index(name="role_created_idx",columns={"created"}),
 * @ORM\Index(name="role_updated_idx",columns={"updated"})
 * })
 */
class Role extends AbstractEntityModel
{

    const RELATIONS_MANY_TO_ONE = [];


    /**
     * @ORM\Column(type="string", unique=true) 
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=false) 
     * @var string
     */
    protected $title;


    /**
     * @ORM\OneToMany(targetEntity="\GPDAuth\Entities\User", mappedBy="roles")
     * @var Collection
     */
    protected $users;




    public function __construct()
    {
        parent::__construct();
        $this->users = new ArrayCollection();
    }


    /**
     * Get the value of code
     *
     * @return  string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @param  string  $code
     *
     * @return  self
     */
    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of title
     *
     * @return  string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param  ?string  $title
     *
     * @return  self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of users
     *
     * @return  Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }
}
