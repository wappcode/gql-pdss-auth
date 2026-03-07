<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use GPDCore\Entities\AbstractEntityModelUlid;

#[ORM\Entity]
#[ORM\Table(name: "gpd_auth_jwt_keys")]
class JWTKey extends AbstractEntityModelUlid
{
    #[ORM\Column(name: "kid", type: "string", unique: true, nullable: false)]
    private string $kid;

    #[ORM\Column(name: "algorithm", type: "string", nullable: false)]
    private string $algorithm;

    #[ORM\Column(name: "private_key", type: "text", nullable: false)]
    private string $privateKey;

    #[ORM\Column(name: "public_key", type: "text", nullable: false)]
    private string $publicKey;

    #[ORM\Column(name: "is_active", type: "boolean", nullable: false)]
    private bool $isActive = true;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: false)]
    private DateTime $createdAt;

    public function __construct()
    {
        parent::__construct();
        $this->createdAt = new DateTime();
    }

    public function getKid(): string
    {
        return $this->kid;
    }

    public function setKid(string $kid): self
    {
        $this->kid = $kid;
        return $this;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(string $algorithm): self
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }


    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }


    public function deactivate(): self
    {
        $this->isActive = false;
        return $this;
    }


    public function isActive(): bool
    {
        return $this->isActive;
    }


    public function getPrivateKey()
    {
        return $this->privateKey;
    }


    public function setPrivateKey($privateKey): self
    {
        $this->privateKey = $privateKey;

        return $this;
    }
}
