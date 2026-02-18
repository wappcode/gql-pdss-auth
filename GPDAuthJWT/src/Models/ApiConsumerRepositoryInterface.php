<?php


namespace GPDAuthJWT\Models;

use GPDAuthJWT\Entities\ApiConsumer;

interface ApiConsumerRepositoryInterface
{
    public function findByIdentifier(string $consumerId): ?ApiConsumer;
}
