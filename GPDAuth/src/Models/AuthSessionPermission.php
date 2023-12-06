<?php

namespace GPDAuth\Models;

class AuthSessionPermission
{

    /**
     * Code of the resource
     * Código del recurso
     *
     * @var string
     */
    private $resource;
    /**
     * Access Type
     * Tipo de acceso
     * ALLOW | DENY
     *
     * @var string
     */
    private $access;
    /**
     * Permission Value
     * Valor del permiso
     * CREATE|UPDATE|DELETE|VIEW..
     *
     * @var string
     */
    private $value;
    /**
     * Permission Scope
     * Alcancel o ámbito del permiso
     * ALL...
     *
     * @var ?string
     */
    private $scope;

    public function __construct(string $resource, string $access, string $value, ?string $scope = null)
    {
        $this->resource = $resource;
        $this->access = $access;
        $this->value = $value;
        $this->scope = $scope;
    }

    public function toArray()
    {
        $data = get_object_vars($this);
        return $data;
    }

    /**
     * Get código del recurso
     *
     * @return  string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set código del recurso
     *
     * @param  string  $resource  Código del recurso
     *
     * @return  self
     */
    public function setResource(string $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get aLLOW | DENY
     *
     * @return  string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set aLLOW | DENY
     *
     * @param  string  $access  ALLOW | DENY
     *
     * @return  self
     */
    public function setAccess(string $access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get cREATE|UPDATE|DELETE|VIEW..
     *
     * @return  string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set cREATE|UPDATE|DELETE|VIEW..
     *
     * @param  string  $value  CREATE|UPDATE|DELETE|VIEW..
     *
     * @return  self
     */
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get [string]
     *
     * @return  array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set [string]
     *
     * @param  array  $scope  [string]
     *
     * @return  self
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;

        return $this;
    }
}
