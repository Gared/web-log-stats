<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Model;

use Gared\WebLogStats\Api\Model\HostInfoModel;

class AccessLogInfoAggregationModel
{
    public function __construct(
        private readonly AccessLogLineModel $lineModel,
        private readonly ?string $countryName,
        private readonly ?HostInfoModel $hostInfo,
    ) {
    }

    public function getLineModel(): AccessLogLineModel
    {
        return $this->lineModel;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function getHostInfo(): ?HostInfoModel
    {
        return $this->hostInfo;
    }
}