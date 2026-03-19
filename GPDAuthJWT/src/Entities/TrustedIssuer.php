<?php

declare(strict_types=1);

namespace GPDAuthJWT\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PDSSUtilities\AbstractEntityModelUlid;

#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_trusted_issuers")]
#[ORM\Index(name: "issuer_idx", columns: ["issuer"])]
#[ORM\Index(name: "status_idx", columns: ["status"])]
#[ORM\HasLifecycleCallbacks]
/**
 * Entidad para gestionar Issuers de confianza (Identity Providers)
 * Permite validar JWTs de múltiples proveedores externos
 */
class TrustedIssuer extends AbstractEntityModelUlid
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


    /**
     * Mapeos de roles para este issuer (código externo → código interno)
     * Si se define, solo los roles mapeados serán considerados válidos para los JWTs de este issuer
     * Es obligatorio definirlo si se quieren usar roles en los JWTs de este issuer
     * Si no se define, los usuarios de este issuer no tendrán roles ni permisos
     * @var Collection<TrustedIssuerRoleMapping>
     */
    #[ORM\OneToMany(targetEntity: TrustedIssuerRoleMapping::class, mappedBy: "trustedIssuer", cascade: ["remove"])]
    private Collection $roleMappings;

    public function __construct()
    {
        parent::__construct();
        $this->audiences = new ArrayCollection();
        $this->roleMappings = new ArrayCollection();
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

    /**
     *
     * @return Collection<TrustedIssuerRoleMapping>
     */
    public function getRoleMappings(): Collection
    {
        return $this->roleMappings;
    }

    public function setRoleMappings(Collection $roleMappings): self
    {
        $this->roleMappings = $roleMappings;
        return $this;
    }
}
