<?php

declare(strict_types=1);


namespace GPDAuth\Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use PDSSUtilities\AbstractEntityModel;

#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_users")]
#[ORM\Index(name: "user_username_idx", columns: ["username"])]
#[ORM\Index(name: "user_email_idx", columns: ["email"])]
#[ORM\Index(name: "user_firstname_idx", columns: ["firstname"])]
#[ORM\Index(name: "user_lastname_idx", columns: ["lastname"])]
#[ORM\Index(name: "user_created_idx", columns: ["created"])]
#[ORM\Index(name: "user_updated_idx", columns: ["updated"])]

/**
 * Doctrine Entity For Users
 * Enitidad Doctrine para usuarios
 */
class User extends AbstractEntityModel
{



    #[ORM\Column(type: "string", name: "firstname", nullable: false)]

    protected string $firstName;


    #[ORM\Column(type: "string", name: "lastname", nullable: true)]
    protected ?string $lastName;

    #[ORM\Column(type: "string", name: "email", nullable: true)]
    protected ?string $email;


    #[ORM\Column(type: "string", unique: true, name: "username", nullable: false)]

    protected string $username;


    #[ORM\Column(type: "string", name: "algorithm")]

    protected string $algorithm;


    #[ORM\Column(type: "string", name: "salt", nullable: true)]

    protected ?string $salt;


    #[ORM\Column(type: "string", name: "user_password")]
    protected ?string $password;



    #[ORM\Column(type: "date", name: "password_expiration", nullable: true)]
    protected ?DateTime $passwordExpiration;


    #[ORM\Column(type: "text", nullable: true)]

    protected ?string $picture;

    #[ORM\Column(type: "boolean")]
    protected bool $active;


    #[ORM\Column(type: "datetime", name: "last_login", nullable: true)]
    protected ?DateTime $lastLogin;


    #[ORM\ManyToMany(targetEntity: "\GPDAuth\Entities\Role", inversedBy: "users")]
    #[ORM\JoinTable(name: "gpd_auth_users_roles")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "id")]
    protected Collection $roles;

    public function __construct()
    {
        parent::__construct();
        $this->algorithm = 'sha1';
        $this->active = true;
        $this->roles = new ArrayCollection();
    }




    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }


    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }


    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }


    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }


    public function getSalt(): ?string
    {
        return $this->salt;
    }


    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }


    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    public function getPasswordExpiration(): ?DateTime
    {
        return $this->passwordExpiration;
    }

    public function setPasswordExpiration(?DateTime $passwordExpiration): self
    {
        $this->passwordExpiration = $passwordExpiration;

        return $this;
    }



    public function getActive(): bool
    {
        return $this->active;
    }


    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getFullName(): string
    {
        $firstName = $this->firstName ?? '';
        $lastName = $this->lastName ?? '';
        if (empty($lastName)) {
            return $firstName;
        } else {
            return sprintf("%s %s", $firstName, $lastName);
        }
    }


    public function getRoles(): Collection
    {
        return $this->roles;
    }


    public function setRoles(Collection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    public function getEmail()
    {
        return $this->email;
    }


    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }


    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }
}
