<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;
use GraphQL\Doctrine\Annotation as API;




/**
 * @ORM\Entity()
 * @ORM\Table(name="gpd_auth_resources")
 */
class Resource extends AbstractEntityModel
{

    const RELATIONS_MANY_TO_ONE = [];


    /**
     * @ORM\Column(type="string", unique=true, nullable=false) 
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=false) 
     * @var string
     */
    protected $title;
    /**
     * @ORM\Column(type="string", nullable=true) 
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var ?array
     */
    protected $scopes;

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
     * @param  string  $title
     *
     * @return  self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return  string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param  string  $description
     *
     * @return  self
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of scopes
     * @API\Field(type="?string[]")
     * @return  ?array
     */
    public function getScopes(): ?array
    {
        return $this->scopes;
    }


    /**
     * Set the value of scopes
     *
     * @API\Input(type="?string[]")
     * @param  string  $scopes
     *
     * @return  self
     */
    public function setScopes(?array $scopes)
    {

        $this->scopes = $scopes;

        return $this;
    }
}
