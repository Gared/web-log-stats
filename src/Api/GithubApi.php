<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Api;

use GuzzleHttp\BodySummarizer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;

class GithubApi
{
    private Client $client;

    public function __construct(string $token)
    {
        $stack = new HandlerStack(Utils::chooseHandler());
        $stack->push(Middleware::httpErrors(new BodySummarizer(1000)), 'http_errors');

        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
                'User-Agent' => 'Gared (project: web-log-stats)',
            ],
            'handler' => $stack,
        ]);
    }

    public function getMeta(): ?array
    {
        try {
            $response = $this->client->get('meta');
            $body = (string) $response->getBody();
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            var_dump($e->getMessage());
        }

        return null;
    }
}