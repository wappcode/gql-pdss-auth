<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;

#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_client_grants")]
#[ORM\UniqueConstraint(name: "uq_client_grant", columns: ["consumer_id", "grant_type"])]
#[ORM\HasLifecycleCallbacks]
class ApiConsumerGrants extends AbstractEntityModel
{

    #[ORM\ManyToOne(targetEntity: ApiConsumer::class)]
    #[ORM\JoinColumn(name: "consumer_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ApiConsumer $consumer;

    #[ORM\Column(name: "grant_type", type: "string", length: 50, nullable: false)]
    private string $grantType;

    #[ORM\Column(name: "enabled", type: "boolean", nullable: false)]
    private bool $enabled = true;

    public function getConsumer(): ApiConsumer
    {
        return $this->consumer;
    }

    public function setConsumer(ApiConsumer $consumer): self
    {
        $this->consumer = $consumer;
        return $this;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setGrantType(string $grantType): self
    {
        $this->grantType = $grantType;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }
}
