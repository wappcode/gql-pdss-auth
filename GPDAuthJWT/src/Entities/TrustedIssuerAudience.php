<?php

declare(strict_types=1);

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModelStringId;

#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_trusted_issuer_audiences")]
#[ORM\Index(name: "trusted_issuer_idx", columns: ["trusted_issuer_id"])]
#[ORM\Index(name: "status_idx", columns: ["status"])]
#[ORM\UniqueConstraint(name: "uq_issuer_audience", columns: ["trusted_issuer_id", "audience"])]
#[ORM\HasLifecycleCallbacks]

/**
 * Entidad para gestionar audiencias permitidas por cada Issuer de confianza
 * Define qué valores de 'aud' (audience) son válidos para cada Identity Provider
 */
class TrustedIssuerAudience extends AbstractEntityModelStringId
{


    /**
     * Relación con el Issuer de confianza
     */
    #[ORM\ManyToOne(targetEntity: TrustedIssuer::class)]
    #[ORM\JoinColumn(name: "trusted_issuer_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected TrustedIssuer $trustedIssuer;

    /**
     * Audience claim (aud) - Identifica a quién está dirigido el JWT
     */
    #[ORM\Column(type: "string", length: 255, nullable: false)]
    protected string $audience;

    /**
     * Estado de la audiencia - active: habilitado, disabled: deshabilitado
     */
    #[ORM\Column(type: "string", length: 20, nullable: false, options: ["default" => "active"])]
    protected string $status = 'active';


    public function getTrustedIssuer(): TrustedIssuer
    {
        return $this->trustedIssuer;
    }

    public function setTrustedIssuer(TrustedIssuer $trustedIssuer): self
    {
        $this->trustedIssuer = $trustedIssuer;
        return $this;
    }

    public function getAudience(): string
    {
        return $this->audience;
    }

    public function setAudience(string $audience): self
    {
        $this->audience = $audience;
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


    /**
     * Verifica si la audiencia está activa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
