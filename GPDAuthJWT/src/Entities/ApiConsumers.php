<?php

namespace GPDAuthJWT\Entities;

use GPDCore\Entities\AbstractEntityModelStringId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_consumers")]
class ApiConsumers extends AbstractEntityModelStringId
{
  #[ORM\Column(name: "identifier", type: "string", length: 100, unique: true, nullable: false)]
  private string $identifier;

  #[ORM\Column(name: "secret_hash", type: "string", length: 255, nullable: false)]
  private string $secretHash;

  #[ORM\Column(name: "status", type: "string", length: 20, nullable: false)]
  private string $status;

  #[ORM\Column(name: "revoked_at", type: "datetime", nullable: true)]
  private ?DateTime $revokedAt = null;

  #[ORM\ManyToMany(targetEntity: ApiPermission::class, inversedBy: "consumers")]
  #[ORM\JoinTable(name: "gpd_auth_api_consumer_permissions")]
  #[ORM\JoinColumn(name: "consumer_id", referencedColumnName: "id")]
  #[ORM\InverseJoinColumn(name: "permission_id", referencedColumnName: "id")]
  private Collection $permissions;

  #[ORM\OneToMany(mappedBy: "consumer", targetEntity: ApiConsumerPermissions::class, cascade: ["persist", "remove"])]
  private Collection $consumerPermissions;

  public function __construct()
  {
    parent::__construct();
    $this->permissions = new ArrayCollection();
    $this->consumerPermissions = new ArrayCollection();
  }

  public function getIdentifier(): string
  {
    return $this->identifier;
  }

  public function setIdentifier(string $identifier): self
  {
    $this->identifier = $identifier;
    return $this;
  }

  public function getSecretHash(): string
  {
    return $this->secretHash;
  }

  public function setSecretHash(string $secretHash): self
  {
    $this->secretHash = $secretHash;
    return $this;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): self
  {
    $this->status = $status;
    return $this;
  }

  public function getRevokedAt(): ?DateTime
  {
    return $this->revokedAt;
  }

  public function setRevokedAt(?DateTime $revokedAt): self
  {
    $this->revokedAt = $revokedAt;
    return $this;
  }

  /**
   * Check if the API consumer is active
   */
  public function isActive(): bool
  {
    return $this->status === 'active';
  }

  /**
   * Check if the API consumer is suspended
   */
  public function isSuspended(): bool
  {
    return $this->status === 'suspended';
  }

  /**
   * Check if the API consumer is revoked
   */
  public function isRevoked(): bool
  {
    return $this->status === 'revoked';
  }

  /**
   * Activate the API consumer
   */
  public function activate(): self
  {
    $this->status = 'active';
    $this->revokedAt = null;
    return $this;
  }

  /**
   * Suspend the API consumer
   */
  public function suspend(): self
  {
    $this->status = 'suspended';
    return $this;
  }

  /**
   * Revoke the API consumer
   */
  public function revoke(): self
  {
    $this->status = 'revoked';
    $this->revokedAt = new DateTime();
    return $this;
  }

  /**
   * @return Collection<int, ApiPermission>
   */
  public function getPermissions(): Collection
  {
    return $this->permissions;
  }

  public function addPermission(ApiPermission $permission): self
  {
    if (!$this->permissions->contains($permission)) {
      $this->permissions->add($permission);
    }
    return $this;
  }

  public function removePermission(ApiPermission $permission): self
  {
    $this->permissions->removeElement($permission);
    return $this;
  }

  /**
   * @return Collection<int, ApiConsumerPermissions>
   */
  public function getConsumerPermissions(): Collection
  {
    return $this->consumerPermissions;
  }

  /**
   * Check if consumer has a specific permission
   */
  public function hasPermission(ApiPermission $permission): bool
  {
    return $this->permissions->contains($permission);
  }

  /**
   * Check if consumer has a permission by name
   */
  public function hasPermissionByName(string $permissionName): bool
  {
    foreach ($this->permissions as $permission) {
      if ($permission->getName() === $permissionName) {
        return true;
      }
    }
    return false;
  }
}
