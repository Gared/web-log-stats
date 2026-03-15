<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Api;

use Gared\WebLogStats\Api\Model\HostInfoModel;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;

class ShodanApi
{
    private const CACHE_DIR = __DIR__ . '/../../cache/shodan/host/';
    private const CACHE_GZ_SUFFIX = '.json.gz';

    private array $cache = [];
    private array $unknownIpCache = [];
    private ClientInterface $httpClient;

    private const REQUEST_INTERVAL_SECONDS = 1;
    private ?float $lastRequestTimestamp = null;

    public function __construct(
        private readonly string $apiKey,
        ?ClientInterface        $httpClient = null,
    )
    {
        $this->httpClient = $httpClient ?? new Client();
    }

    public function getHostInfo(string $ip): ?HostInfoModel
    {
        if (array_key_exists($ip, $this->cache)) {
            return $this->cache[$ip];
        }

        if (array_key_exists($ip, $this->unknownIpCache)) {
            return null;
        }

        $cacheGzPath = self::CACHE_DIR . $ip . self::CACHE_GZ_SUFFIX;

        if (file_exists($cacheGzPath)) {
            $content = $this->readCachedJson($cacheGzPath);
            if ($content === null) {
                $this->unknownIpCache[$ip] = null;
                return null;
            }

            try {
                return $this->createModel($ip, json_decode($content, true, 512, JSON_THROW_ON_ERROR));
            } catch (JsonException) {
                $this->unknownIpCache[$ip] = null;
                return null;
            }
        }

        $this->throttle();
        $this->lastRequestTimestamp = microtime(true);

        try {
            $response = $this->httpClient->request('GET', 'https://api.shodan.io/shodan/host/' . rawurlencode($ip), [
                RequestOptions::QUERY => ['key' => $this->apiKey],
                RequestOptions::HTTP_ERRORS => false,
            ]);
        } catch (GuzzleException) {
            $this->unknownIpCache[$ip] = null;
            return null;
        }

        if ($response->getStatusCode() === 404) {
            $this->unknownIpCache[$ip] = null;
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            var_dump($response->getStatusCode());
            var_dump((string)$response->getBody());
            return null;
        }

        $json = (string)$response->getBody();
        if ($json === '') {
            var_dump('Error empty response');
            return null;
        }

        $compressedJson = gzencode($json, 6, ZLIB_ENCODING_GZIP);
        if ($compressedJson !== false) {
            file_put_contents($cacheGzPath, $compressedJson);
        }

        try {
            return $this->createModel($ip, json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            var_dump($e->getMessage());
            $this->unknownIpCache[$ip] = null;
            return null;
        }
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

    private function throttle(): void
    {
        if ($this->lastRequestTimestamp === null) {
            return;
        }

        $elapsed = microtime(true) - $this->lastRequestTimestamp;
        $wait = self::REQUEST_INTERVAL_SECONDS - $elapsed;

        if ($wait > 0) {
            $actualWait = (int)($wait * 1_000_000) + 200_000;
            usleep($actualWait);
        }
    }

    private function readCachedJson(string $cacheGzPath): ?string
    {
        $compressed = file_get_contents($cacheGzPath);
        if ($compressed === false) {
            return null;
        }

        $decoded = gzdecode($compressed);
        if ($decoded !== false) {
            return $decoded;
        }

        return null;
    }
}
