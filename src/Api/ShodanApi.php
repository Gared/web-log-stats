<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Api;

use Gared\WebLogStats\Api\Model\HostInfoModel;

class ShodanApi
{
    private const CACHE_DIR = __DIR__ . '/../../cache/shodan/host/';

    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    public function getHostInfo(string $ip): ?HostInfoModel
    {
        if (file_exists(self::CACHE_DIR . $ip)) {
            return $this->createModel(json_decode(file_get_contents(self::CACHE_DIR . $ip), true));
        }

        $url = 'https://api.shodan.io/shodan/host/' . $ip . '?key=' . $this->apiKey;
        $json = @file_get_contents($url);

        if ($json === false) {
            return null;
        }

        $fp = fopen(self::CACHE_DIR . $ip, 'w');
        fwrite($fp, $json);
        fclose($fp);

        return $this->createModel(json_decode($json, true));
    }

    private function createModel(array $json): HostInfoModel
    {
        return new HostInfoModel(
            $json['country_name'] ?? null,
            $json['org'] ?? null,
            $json['isp'] ?? null,
        );
    }
}