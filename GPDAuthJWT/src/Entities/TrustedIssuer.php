<?php

declare(strict_types=1);

namespace GPDAuthJWT\Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModelStringId;

#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_trusted_issuers")]
#[ORM\Index(name: "issuer_idx", columns: ["issuer"])]
#[ORM\Index(name: "status_idx", columns: ["status"])]
#[ORM\HasLifecycleCallbacks]
/**
 * Entidad para gestionar Issuers de confianza (Identity Providers)
 * Permite validar JWTs de múltiples proveedores externos
 */
class TrustedIssuer extends AbstractEntityModelStringId
{


    /**
     * Issuer claim (iss) - Identificador único del Identity Provider
     */
    #[ORM\Column(type: "string", length: 255, unique: true, nullable: false)]
    protected string $issuer;

    /**
     * JWKS endpoint del IdP - URL donde obtener las claves públicas
     */
    #[ORM\Column(type: "string", length: 500, name: "jwks_url", nullable: false)]
    protected string $jwksUrl;

    /**
     * Algoritmo esperado para la firma del JWT (RS256 recomendado)
     */
    #[ORM\Column(type: "string", length: 20, nullable: false, options: ["default" => "RS256"])]
    protected string $alg = 'RS256';

    /**
     * Estado del issuer - active: habilitado, disabled: deshabilitado
     */
    #[ORM\Column(type: "string", length: 20, nullable: false, options: ["default" => "active"])]
    protected string $status = 'active';

    /**
     * Nombre descriptivo del issuer
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    protected ?string $name = null;

    /**
     * Descripción del issuer
     */
    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $description = null;

    /**
     * 
     *
     * @var Collection<TrustedIssuerAudience>
     */
    #[ORM\OneToMany(targetEntity: TrustedIssuerAudience::class, mappedBy: "trustedIssuer", cascade: ["remove"])]
    private Collection $audiences;


    public function __construct()
    {
        parent::__construct();
        $this->audiences = new ArrayCollection();
    }
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    public function setIssuer(string $issuer): self
    {
        $this->issuer = $issuer;
        return $this;
    }

    public function getJwksUrl(): string
    {
        return $this->jwksUrl;
    }

    public function setJwksUrl(string $jwksUrl): self
    {
        $this->jwksUrl = $jwksUrl;
        return $this;
    }

    public function getAlg(): string
    {
        return $this->alg;
    }

    public function setAlg(string $alg): self
    {
        $this->alg = $alg;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Verifica si el issuer está activo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }


    public function getAudiences()
    {
        return $this->audiences;
    }


    public function setAudiences($audiences)
    {
        $this->audiences = $audiences;

        return $this;
    }
}
