<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Api;

use Gared\WebLogStats\Api\Model\HostInfoModel;
use JsonException;

class ShodanApi
{
    private const CACHE_DIR = __DIR__ . '/../../cache/shodan/host/';

    private array $cache = [];
    private array $unknownIpCache = [];

    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    public function getHostInfo(string $ip): ?HostInfoModel
    {
        if (array_key_exists($ip, $this->cache)) {
            return $this->cache[$ip];
        }

        if (array_key_exists($ip, $this->unknownIpCache)) {
            return null;
        }

        if (file_exists(self::CACHE_DIR . $ip)) {
            $content = file_get_contents(self::CACHE_DIR . $ip);
            try {
                return $this->createModel($ip, json_decode($content, true, 512, JSON_THROW_ON_ERROR));
            } catch (JsonException $e) {
                var_dump($e->getMessage());
                return null;
            }
        }

        $url = 'https://api.shodan.io/shodan/host/' . $ip . '?key=' . $this->apiKey;
        $json = @file_get_contents($url);

        if ($json === false || $json === null) {
            $this->unknownIpCache[$ip] = null;
            return null;
        }

        $fp = fopen(self::CACHE_DIR . $ip, 'w');
        fwrite($fp, $json);
        fclose($fp);

        return $this->createModel($ip, json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }

    private function createModel(string $ip, array $json): HostInfoModel
    {
        $model = new HostInfoModel(
            $json['country_name'] ?? null,
            $json['org'] ?? null,
            $json['isp'] ?? null,
        );
        $this->cache[$ip] = $model;
        return $model;
    }
}