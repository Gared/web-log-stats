<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Service;

use Gared\WebLogStats\Api\GithubApi;
use Gared\WebLogStats\Api\ShodanApi;
use Gared\WebLogStats\Model\AccessLogInfoAggregationModel;
use Gared\WebLogStats\Reader\NginxReader;
use GeoIp2\Database\Reader;
use Symfony\Component\Console\Helper\ProgressBar;

class CollectDataService
{
    private NginxReader $nginxReader;
    private Reader $ipDatabaseReader;
    private ?ShodanApi $shodanApi = null;
    private ?GithubApi $githubApi = null;

    public function __construct(string $pathIpDatabase, ?string $shodanApiKey)
    {
        $this->nginxReader = new NginxReader();
        $this->ipDatabaseReader = new Reader($pathIpDatabase);
        if ($shodanApiKey !== null) {
            $this->shodanApi = new ShodanApi($shodanApiKey);
        }

        $this->githubApi = new GithubApi();
    }

    /**
     * @return AccessLogInfoAggregationModel[]
     */
    public function collect(string $path, ProgressBar $progressBar): array
    {
        $ignoreIps = null;
        if ($this->githubApi !== null) {
            $metaData = $this->githubApi->getMeta();
            if ($metaData !== null) {
                $ignoreIps = $metaData['actions'];
            }
        }

        $data = $this->nginxReader->readLogFile(
            $path,
            $ignoreIps,
        );

        $progressBar->setMaxSteps(count($data));

        $result = [];
        foreach ($data as $item) {
            $hostInfo = null;
            if ($this->shodanApi !== null) {
                $hostInfo = $this->shodanApi->getHostInfo($item->getIp());
            }

            $result[] = new AccessLogInfoAggregationModel(
                $item,
                $this->ipDatabaseReader->country($item->getIp())->country->name,
                $hostInfo,
            );
            $progressBar->advance();
        }

        return $result;
    }
}
