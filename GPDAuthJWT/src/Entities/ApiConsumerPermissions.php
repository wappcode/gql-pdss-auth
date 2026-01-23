<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_consumer_permissions")]
class ApiConsumerPermissions
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ApiConsumers::class, inversedBy: "consumerPermissions")]
    #[ORM\JoinColumn(name: "consumer_id", referencedColumnName: "id", nullable: false)]
    private ApiConsumers $consumer;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ApiPermission::class, inversedBy: "consumerPermissions")]
    #[ORM\JoinColumn(name: "permission_id", referencedColumnName: "id", nullable: false)]
    private ApiPermission $permission;

    #[ORM\Column(name: "granted_at", type: "datetime", nullable: false)]
    private DateTime $grantedAt;

    public function __construct(ApiConsumers $consumer, ApiPermission $permission)
    {
        $this->consumer = $consumer;
        $this->permission = $permission;
        $this->grantedAt = new DateTime();
    }

    public function getConsumer(): ApiConsumers
    {
        return $this->consumer;
    }

    public function setConsumer(ApiConsumers $consumer): self
    {
        $this->consumer = $consumer;
        return $this;
    }

    public function getPermission(): ApiPermission
    {
        return $this->permission;
    }

    public function setPermission(ApiPermission $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function getGrantedAt(): DateTime
    {
        return $this->grantedAt;
    }

    public function setGrantedAt(DateTime $grantedAt): self
    {
        $this->grantedAt = $grantedAt;
        return $this;
    }

    /**
     * Check if this permission grant is recent (within last 24 hours)
     */
    public function isRecentlyGranted(): bool
    {
        $oneDayAgo = new DateTime('-1 day');
        return $this->grantedAt > $oneDayAgo;
    }
}
