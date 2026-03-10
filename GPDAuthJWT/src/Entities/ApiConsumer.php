<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;
use GPDAuthJWT\Models\ApiConsumerStatus;
use GPDCore\Entities\AbstractEntityModelUlid;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_consumers")]
class ApiConsumer extends AbstractEntityModelUlid
{
  #[ORM\Column(name: "identifier", type: "string", length: 100, unique: true, nullable: false)]
  private string $identifier;

  #[ORM\Column(name: "name", type: "string", length: 100, unique: true, nullable: false)]
  private string $name;

  #[ORM\Column(name: "secret_hash", type: "string", length: 255, nullable: false)]
  private string $secretHash;

  #[ORM\Column(name: "status", type: "string", length: 20, nullable: false, enumType: ApiConsumerStatus::class)]
  private ApiConsumerStatus $status;

  #[ORM\Column(name: "revoked_at", type: "datetime", nullable: true)]
  private ?DateTime $revokedAt = null;

  #[ORM\OneToMany(mappedBy: "consumer", targetEntity: ApiConsumerPermission::class)]
  private Collection $permissions;

  public function __construct()
  {
    parent::__construct();
    $this->permissions = new ArrayCollection();
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

  public function getStatus(): ApiConsumerStatus
  {
    return $this->status;
  }

  public function setStatus(ApiConsumerStatus $status): self
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
    return $this->status === ApiConsumerStatus::ACTIVE;
  }

  /**
   * Check if the API consumer is suspended
   */
  public function isSuspended(): bool
  {
    return $this->status === ApiConsumerStatus::SUSPENDED;
  }

  /**
   * Check if the API consumer is revoked
   */
  public function isRevoked(): bool
  {
    return $this->status === ApiConsumerStatus::REVOKED;
  }

  /**
   * Activate the API consumer
   */
  public function activate(): self
  {
    $this->status = ApiConsumerStatus::ACTIVE;
    $this->revokedAt = null;
    return $this;
  }

  /**
   * Suspend the API consumer
   */
  public function suspend(): self
  {
    $this->status = ApiConsumerStatus::SUSPENDED;
    return $this;
  }

  /**
   * Revoke the API consumer
   */
  public function revoke(): self
  {
    $this->status = ApiConsumerStatus::REVOKED;
    $this->revokedAt = new DateTime();
    return $this;
  }

  /**
   * @return Collection<int, ApiConsumerPermission>
   */
  public function getPermissions(): Collection
  {
    return $this->permissions;
  }

  public function addPermission(ApiConsumerPermission $permission): self
  {
    if (!$this->permissions->contains($permission)) {
      $this->permissions->add($permission);
    }
    return $this;
  }

  public function removePermission(ApiConsumerPermission $permission): self
  {
    $this->permissions->removeElement($permission);
    return $this;
  }


  /**
   * Check if consumer has a specific permission
   */
  public function hasPermission(ApiConsumerPermission $permission): bool
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



  /**
   * Get the value of name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the value of name
   *
   * @return  self
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }
}
