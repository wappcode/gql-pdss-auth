<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;
use GraphQL\Doctrine\Annotation as API;




#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_resources")]
class Resource extends AbstractEntityModel
{
    #[ORM\Column(type: "string", unique: true, nullable: false)]


    protected $code;

    #[ORM\Column(type: "string", nullable: false)]


    protected $title;
    #[ORM\Column(type: "string", nullable: true)]


    protected $description;

    #[ORM\Column(type: "json", nullable: true)]


    protected $scopes;



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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getScopes(): ?array
    {
        return $this->scopes;
    }

    public function setScopes(?array $scopes)
    {

        $this->scopes = $scopes;

        return $this;
    }
}
