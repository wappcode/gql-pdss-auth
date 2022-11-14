<?php

declare(strict_types=1);


namespace GPDAuth\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use Doctrine\Common\Collections\Collection;
use GPDCore\Entities\AbstractEntityModel;

/**
 * Doctrine Entity For Users
 * Enitidad Doctrine para usuarios
 * @ORM\Entity()
 * @ORM\Table(name="gpd_auth_users", indexes={
 * @ORM\Index(name="user_username_idx",columns={"username"}),
 * @ORM\Index(name="user_email_idx",columns={"email"}),
 * @ORM\Index(name="user_firstname_idx",columns={"firstname"}),
 * @ORM\Index(name="user_lastname_idx",columns={"lastname"}),
 * @ORM\Index(name="user_created_idx",columns={"created"}),
 * @ORM\Index(name="user_updated_idx",columns={"updated"})
 * })
 * 
 */
class User extends AbstractEntityModel
{

    const RELATIONS_MANY_TO_ONE = [];

    /**
     * @ORM\Column(type="string", name="firstname", nullable=false) 
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", name="lastname", nullable=true) 
     * @var ?string
     */
    protected $lastName;


    /**
     * @ORM\Column(type="string", name="email", nullable=true) 
     * @var ?string
     */
    protected $email;

    /**
     * @ORM\Column(type="string",unique=true, name="username", nullable=false) 
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string", name="algorithm") 
     * @var string
     */
    protected $algorithm;

    /**
     * @ORM\Column(type="string", name="salt", nullable=true) 
     * @var ?string
     */
    protected $salt;

    /**
     * @ORM\Column(type="string", name="user_password")
     * @var string
     */
    protected $password;


    /**
     * @ORM\Column(type="date", name="password_expiration", nullable=true)
     * @var ?DateTime
     */
    protected $passwordExpiration;

    /**
     * @ORM\Column(type="text", nullable=true) 
     * @var ?string
     */
    protected $photo;

    /**
     *
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $active;

    /**
     * @ORM\Column(type="datetime",name="last_login", nullable=true)
     * @var ?DateTime
     */
    protected $lastLogin;

    /**
     * @ORM\ManyToMany(targetEntity="\GPDAuth\Entities\Role", inversedBy="users")
     * @ORM\JoinTable(name="gpd_auth_users_roles",
     * joinColumns={
     *  @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * },
     * inverseJoinColumns={
     *  @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * }
     * )
     * @var Collection
     */
    protected $roles;

    public function __construct()
    {
        parent::__construct();
        $this->algorithm = 'sha1';
        $this->active = true;
    }


    /**
     * Get the value of firstName
     *
     * @return  string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @param  string  $firstName
     *
     * @return  self
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get the value of lastName
     *
     * @return  ?string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @param  ?string  $lastName
     *
     * @return  self
     */
    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }


    /**
     * Get the value of username
     *
     * @return  string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @param  string  $username
     *
     * @return  self
     */
    public function setUsername(string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of algorithm
     *
     * @return  string
     * @API\Exclude
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Set the value of algorithm
     *
     * @API\Exclude
     * @param  string  $algorithm
     *
     * @return  self
     */
    public function setAlgorithm(string $algorithm)
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * Get the value of salt
     * @API\Exclude
     * @return  ?string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * Set the value of salt
     *
     * @API\Exclude
     * @param  ?string  $salt
     *
     * @return  self
     */
    public function setSalt(?string $salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get the value of password
     * @API\Exclude
     * @return  string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @param  string  $password
     *
     * @return  self
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of passwordExpiration
     *
     * @return  ?DateTime
     */
    public function getPasswordExpiration(): ?DateTime
    {
        return $this->passwordExpiration;
    }

    /**
     * Set the value of passwordExpiration
     *
     * @param  ?DateTime  $passwordExpiration
     *
     * @return  self
     */
    public function setPasswordExpiration(?DateTime $passwordExpiration)
    {
        $this->passwordExpiration = $passwordExpiration;

        return $this;
    }

    /**
     * Get the value of photo
     *
     * @return  ?string
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * Set the value of photo
     *
     * @param  ?string  $photo
     *
     * @return  self
     */
    public function setPhoto(?string $photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get the value of active
     *
     * @return  bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @param  ?bool  $active
     *
     * @return  self
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of lastLogin
     *
     * @return  ?DateTime
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    /**
     * Set the value of lastLogin
     *
     * @API\Exclude
     * @param  ?DateTime  $lastLogin
     *
     * @return  self
     */
    public function setLastLogin(?DateTime $lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }



    /**
     * Get the value of fullname
     *
     * @return  string
     */
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


    /**
     * 
     *
     * @return  Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     *
     * @API\Input(type="id[]")
     * @param  Collection  $roles 
     *
     * @return  self
     */
    public function setRoles(Collection $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the value of email
     *
     * @return  ?string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param  ?string  $email
     *
     * @return  self
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;

        return $this;
    }
}
