<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Api\Model;

class HostInfoModel
{
    public function __construct(
        private readonly ?string $countryName,
        private readonly ?string $org,
        private readonly ?string $isp,
    ) {
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function getOrg(): ?string
    {
        return $this->org;
    }

    public function getIsp(): ?string
    {
        return $this->isp;
    }
}