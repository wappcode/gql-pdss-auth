<?php

namespace GPDAuthJWT\Services;

use GPDAuthJWT\Entities\JWTKey;
use GPDCore\Library\IContextService;

class JWTKidRepository implements \GPDAuthJWT\Models\JWTKidRepositoryInterface
{
    private IContextService $context;

    public function __construct(IContextService $context)
    {
        $this->context = $context;
    }

    public function getKeyByKid(string $kid): ?JWTKey
    {
        $entityManager = $this->context->getEntityManager();
        $key = $entityManager->createQueryBuilder()->from(JWTKey::class, 'k')
            ->where('k.kid = :kid')
            ->setParameter('kid', $kid)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $key;
    }
}
