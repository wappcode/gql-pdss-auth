<?php

namespace GPDAuthJWT\Entities;

use GPDCore\Entities\AbstractEntityModelStringId;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/** Usado para IdP local para Machine to Machine M2M */
#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_api_consumers")]
class ApiConsumer extends AbstractEntityModelStringId
{
  #[ORM\Column(name: "identifier", type: "string", length: 100, unique: true, nullable: false)]
  private string $identifier;

  #[ORM\Column(name: "secret_hash", type: "string", length: 255, nullable: false)]
  private string $secretHash;

  /**
   * revoked, active,suspended
   *
   * @var string
   */
  #[ORM\Column(name: "status", type: "string", length: 20, nullable: false)]
  private string $status;

  #[ORM\Column(name: "revoked_at", type: "datetime", nullable: true)]
  private ?DateTime $revokedAt = null;

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
}
