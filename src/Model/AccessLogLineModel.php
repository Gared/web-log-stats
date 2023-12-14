<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Model;

class AccessLogLineModel
{
    public function __construct(
        private readonly string $ip,
        private readonly ?string $userAgent,
    ) {
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}