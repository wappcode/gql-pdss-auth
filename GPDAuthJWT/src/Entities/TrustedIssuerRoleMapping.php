<?php

namespace GPDAuthJWT\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;

#[ORM\Entity()]
#[ORM\Table(name: "gpd_auth_trusted_issuer_roles")]
#[ORM\UniqueConstraint(name: "trusted_issuer_role_unique", columns: ["trusted_issuer_id", "external_role_code"])]
class TrustedIssuerRoleMapping extends AbstractEntityModel
{

    #[ORM\ManyToOne(targetEntity: TrustedIssuer::class, inversedBy: "roleMappings")]
    #[ORM\JoinColumn(name: "trusted_issuer_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected TrustedIssuer $trustedIssuer;
    #[ORM\Column(type: "string", length: 255, nullable: false, name: "external_role_code")]
    protected string $externalRoleCode;
    #[ORM\Column(type: "string", length: 255, nullable: false, name: "internal_role_code")]
    protected string $internalRoleCode;

    /**
     * Get the value of trustedIssuer
     */
    public function getTrustedIssuer(): TrustedIssuer
    {
        return $this->trustedIssuer;
    }

    /**
     * Set the value of trustedIssuer
     *
     * @return  self
     */
    public function setTrustedIssuer(TrustedIssuer $trustedIssuer): self
    {
        $this->trustedIssuer = $trustedIssuer;

        return $this;
    }

    /**
     * Get the value of externalRoleCode
     */
    public function getExternalRoleCode(): string
    {
        return $this->externalRoleCode;
    }

    /**
     * Set the value of externalRoleCode
     *
     * @return  self
     */
    public function setExternalRoleCode(string $externalRoleCode): self
    {
        $this->externalRoleCode = $externalRoleCode;

        return $this;
    }



    /**
     * Get the value of internalRoleCode
     */
    public function getInternalRoleCode(): string
    {
        return $this->internalRoleCode;
    }

    /**
     * Set the value of internalRoleCode
     *
     * @return  self
     */
    public function setInternalRoleCode(string $internalRoleCode): self
    {
        $this->internalRoleCode = $internalRoleCode;

        return $this;
    }
}
