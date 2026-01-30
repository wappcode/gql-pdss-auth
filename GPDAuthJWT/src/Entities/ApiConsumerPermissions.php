<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use GPDCore\Entities\AbstractEntityModel;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_consumer_permissions")]
#[ORM\HasLifecycleCallbacks]
class ApiConsumerPermissions extends AbstractEntityModel
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ApiConsumer::class, inversedBy: "consumerPermissions")]
    #[ORM\JoinColumn(name: "consumer_id", referencedColumnName: "id", nullable: false)]
    private ApiConsumer $consumer;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ApiPermission::class, inversedBy: "consumerPermissions")]
    #[ORM\JoinColumn(name: "permission_id", referencedColumnName: "id", nullable: false)]
    private ApiPermission $permission;

    #[ORM\Column(name: "granted_at", type: "datetime", nullable: false)]
    private DateTime $grantedAt;

    public function __construct(ApiConsumer $consumer, ApiPermission $permission)
    {
        $this->consumer = $consumer;
        $this->permission = $permission;
        $this->grantedAt = new DateTime();
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

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateGrantedAt(): void
    {
        $this->grantedAt = new DateTime();
    }
}
