<?php

namespace Modules\TripManagement\Service\Interface;

use App\Service\BaseServiceInterface;

interface TempTripNotificationServiceInterface extends BaseServiceInterface
{
    public function getData(array $data = []): mixed;

    public function createMany(array $data): mixed;
}
