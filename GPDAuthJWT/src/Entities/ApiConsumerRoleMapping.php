<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use PDSSUtilities\AbstractEntityModel;

#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_api_consumer_roles")]
#[ORM\UniqueConstraint(name: "api_consumer_role_unique", columns: ["api_consumer_id", "external_role_code"])]
class ApiConsumerRoleMapping extends AbstractEntityModel
{

    #[ORM\ManyToOne(targetEntity: ApiConsumer::class)]
    #[ORM\JoinColumn(name: "api_consumer_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected ApiConsumer $apiConsumer;
    #[ORM\Column(type: "string", length: 255, nullable: false, name: "external_role_code")]
    protected string $externalRoleCode;
    #[ORM\Column(type: "string", length: 255, nullable: false, name: "internal_role_code")]
    protected string $internalRoleCode;

    /**
     * Get the value of apiConsumer
     */
    public function getApiConsumer(): ApiConsumer
    {
        return $this->apiConsumer;
    }

    /**
     * Set the value of apiConsumer
     *
     * @return  self
     */
    public function setApiConsumer(ApiConsumer $apiConsumer): self
    {
        $this->apiConsumer = $apiConsumer;

        return $this;
    }

    /**
     * Get the value of externalRoleCode
     */
    public function getExternalRoleCode(): string
    {
        return $this->externalRoleCode;
    }

    /**
     * Set the value of externalRoleCode
     *
     * @return  self
     */
    public function setExternalRoleCode(string $externalRoleCode): self
    {
        $this->externalRoleCode = $externalRoleCode;

        return $this;
    }



    /**
     * Get the value of internalRoleCode
     */
    public function getInternalRoleCode(): string
    {
        return $this->internalRoleCode;
    }

    /**
     * Set the value of internalRoleCode
     *
     * @return  self
     */
    public function setInternalRoleCode(string $internalRoleCode): self
    {
        $this->internalRoleCode = $internalRoleCode;

        return $this;
    }
}
