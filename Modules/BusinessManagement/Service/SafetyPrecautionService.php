<?php

namespace Modules\BusinessManagement\Service;

use App\Service\BaseService;
use Modules\BusinessManagement\Repository\SafetyPrecautionRepositoryInterface;
use Modules\BusinessManagement\Service\Interface\SafetyPrecautionServiceInterface;

class SafetyPrecautionService extends BaseService implements SafetyPrecautionServiceInterface
{
    public function __construct(SafetyPrecautionRepositoryInterface $safetyPrecautionRepository)
    {
        parent::__construct($safetyPrecautionRepository);
    }
}
