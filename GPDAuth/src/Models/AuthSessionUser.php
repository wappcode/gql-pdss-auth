<?php

namespace GPDAuth\Models;

class AuthSessionUser
{

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $fullName;
    /**
     * Undocumented variable
     *
     * @var ?string
     */
    private $firstName
        /**
     * Undocumented variable
     *
     * @var string
     */
    ;
    private $username;
    /**
     * Undocumented variable
     *
     * @var ?string
     */
    private $lastName;
    /**
     * Undocumented variable
     *
     * @var ?string
     */
    private $email;
    /**
     * Undocumented variable
     *
     * @var ?string
     */
    private $picture;


    public function toArray(): array
    {
        $data = get_object_vars($this);
        return $data;
    }
    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $fullName  Undocumented variable
     *
     * @return  self
     */
    public function setFullName(string $fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  ?string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $firstName  Undocumented variable
     *
     * @return  self
     */
    public function setFirstName(?string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  ?string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set undocumented variable
     *
     * @param  ?string  $lastName  Undocumented variable
     *
     * @return  self
     */
    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  ?string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set undocumented variable
     *
     * @param  ?string  $email  Undocumented variable
     *
     * @return  self
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  ?string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set undocumented variable
     *
     * @param  ?string  $picture  Undocumented variable
     *
     * @return  self
     */
    public function setPicture(?string $picture)
    {
        $this->picture = $picture;

        return $this;
    }
}
