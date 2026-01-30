<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GPDAuth\Entities\Resource;
use GPDCore\Entities\AbstractEntityModel;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_permissions")]
class ApiPermission extends AbstractEntityModel
{
    #[ORM\Column(name: "name", type: "string", length: 100, unique: true, nullable: false)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Resource::class)]
    #[ORM\JoinColumn(name: "resource_id", referencedColumnName: "id", nullable: false)]
    private Resource $resource;

    /**
     * view,create,updated,delete
     *
     * @var string
     */
    #[ORM\Column(name: "value", type: "string", length: 50, unique: true, nullable: false)]
    private string $value;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: ApiConsumer::class, mappedBy: "permissions")]
    private Collection $consumers;

    public function __construct()
    {
        parent::__construct();
        $this->consumers = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, ApiConsumer>
     */
    public function getConsumers(): Collection
    {
        return $this->consumers;
    }

    public function addConsumer(ApiConsumer $consumer): self
    {
        if (!$this->consumers->contains($consumer)) {
            $this->consumers->add($consumer);
            $consumer->addPermission($this);
        }
        return $this;
    }

    public function removeConsumer(ApiConsumer $consumer): self
    {
        if ($this->consumers->removeElement($consumer)) {
            $consumer->removePermission($this);
        }
        return $this;
    }

    /**
     * Get the value of value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }
}
