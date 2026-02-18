<?php


namespace GPDAuthJWT\Services;

use Doctrine\ORM\EntityManager;
use GPDAuthJWT\Entities\ApiConsumer;
use GPDAuthJWT\Models\ApiConsumerRepositoryInterface;

class ApiConsumerRepository implements ApiConsumerRepositoryInterface
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findByIdentifier(string $identifier): ?ApiConsumer
    {
        /** @var ApiConsumer | null */
        $consumer = $this->entityManager->getRepository(ApiConsumer::class)->findOneBy(['identifier' => $identifier]);
        return $consumer;
    }
}
