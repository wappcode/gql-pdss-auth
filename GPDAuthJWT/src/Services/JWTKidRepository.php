<?php

namespace GPDAuthJWT\Services;

use GPDAuthJWT\Entities\JWTKey;
use GPDCore\Contracts\AppContextInterface;

class JWTKidRepository implements \GPDAuthJWT\Contracts\JWTKidRepositoryInterface
{
    private AppContextInterface $context;

    public function __construct(AppContextInterface $context)
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
