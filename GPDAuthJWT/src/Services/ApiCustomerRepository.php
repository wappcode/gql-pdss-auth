<?php


namespace GPDAuthJWT\Models;

use Doctrine\ORM\EntityManager;
use GPDAuthJWT\Entities\ApiConsumer;

class ApiCustomerRepository implements ApiCustomerRepositoryInterface
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findByIdentifier(string $identifier): ?ApiConsumer
    {
        /** @var ApiConsumer | null */
        $customer = $this->entityManager->getRepository(ApiConsumer::class)->findOneBy(['identifier' => $identifier]);
        return $customer;
    }
}
