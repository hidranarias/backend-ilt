<?php

namespace Pyz\Zed\Training;

use Pyz\Shared\Training\TrainingConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class TrainingConfig extends AbstractBundleConfig
{
    public function getMyDefaultStore(): string
    {

        return $this->get(TrainingConstants::MY_DEFAULT_STORE);
    }
}
