<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use PDSSUtilities\AbstractEntityModel;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_permissions")]
class ApiConsumerPermission extends AbstractEntityModel
{
    #[ORM\Column(name: "name", type: "string", length: 100, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: "string", length: 255, nullable: false, name: "resource_code")]
    private string $resourceCode;

    /**
     * view,create,updated,delete,all
     *
     * @var string
     */
    #[ORM\Column(name: "value", type: "string", length: 50, unique: true, nullable: false)]
    private string $value;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    private ?string $description = null;

    /**
     *
     * @var \DateTime
     */
    #[ORM\Column(name: "granted_at", type: "datetime", nullable: false)]
    private \DateTime $grantedAt;

    /**
     *
     * @var ApiConsumer
     */
    #[ORM\ManyToOne(targetEntity: ApiConsumer::class, inversedBy: "permissions")]
    #[ORM\JoinColumn(name: "consumer_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ApiConsumer $consumer;

    public function __construct()
    {
        parent::__construct();
        $this->grantedAt = new \DateTime();
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
     * Get the value of value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of resourceCode
     */
    public function getResourceCode(): string
    {
        return $this->resourceCode;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */
    public function setResourceCode(string $resourceCode): self
    {
        $this->resourceCode = $resourceCode;

        return $this;
    }
    public function getConsumer(): ApiConsumer
    {
        return $this->consumer;
    }
    public function setConsumer(ApiConsumer $consumer): self
    {
        $this->consumer = $consumer;
        return $this;
    }
    public function getGrantedAt(): \DateTime
    {
        return $this->grantedAt;
    }
    public function setGrantedAt(\DateTime $grantedAt): self
    {
        $this->grantedAt = $grantedAt;
        return $this;
    }
}
