<?php


namespace GPDAuthJWT\Models;

use GPDAuthJWT\Entities\ApiConsumer;

interface ApiCustomerRepositoryInterface
{
    public function findByIdentifier(string $customerId): ?ApiConsumer;
}
