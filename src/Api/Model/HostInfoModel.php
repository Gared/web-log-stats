<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Api\Model;

class HostInfoModel
{
    /**
     * @param array $data
     */
    // @phpstan-ignore-next-line
    public function __construct(
        private readonly array $data,
    ) {
    }

    public function getCountryName(): ?string
    {
        return $this->data['country_name'] ?? null;
    }

    public function getOrg(): ?string
    {
        return $this->data['org'] ?? null;
    }

    public function getIsp(): ?string
    {
        return $this->data['isp'] ?? null;
    }
}