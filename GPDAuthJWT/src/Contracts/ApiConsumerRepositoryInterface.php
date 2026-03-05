<?php


namespace GPDAuthJWT\Contracts;

use GPDAuthJWT\Entities\ApiConsumer;

interface ApiConsumerRepositoryInterface
{
    public function findByIdentifier(string $consumerId): ?ApiConsumer;
    public function getAllowedPermissions(ApiConsumer $consumer, array $permissions): array;
}
