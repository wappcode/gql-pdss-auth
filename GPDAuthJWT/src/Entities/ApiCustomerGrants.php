<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;

#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_client_grants")]
#[ORM\UniqueConstraint(name: "uq_client_grant", columns: ["client_id", "grant_type"])]
#[ORM\HasLifecycleCallbacks]
class ApiCustomerGrants extends AbstractEntityModel
{

    #[ORM\ManyToOne(targetEntity: ApiConsumer::class)]
    #[ORM\JoinColumn(name: "customer_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ApiConsumer $customer;

    #[ORM\Column(name: "grant_type", type: "string", length: 50, nullable: false)]
    private string $grantType;

    #[ORM\Column(name: "enabled", type: "boolean", nullable: false)]
    private bool $enabled = true;

    public function getCustomer(): ApiConsumer
    {
        return $this->customer;
    }

    public function setCustomer(ApiConsumer $customer): self
    {
        $this->customer = $customer;
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
