<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Service;

use Gared\WebLogStats\Api\ShodanApi;
use Gared\WebLogStats\Model\AccessLogInfoAggregationModel;
use Gared\WebLogStats\Reader\NginxReader;
use GeoIp2\Database\Reader;
use Symfony\Component\Console\Helper\ProgressBar;

class CollectDataService
{
    private NginxReader $nginxReader;
    private Reader $ipDatabaseReader;
    private ShodanApi $shodanApi;

    public function __construct(string $pathIpDatabase, string $shodanApiKey)
    {
        $this->nginxReader = new NginxReader();
        $this->ipDatabaseReader = new Reader($pathIpDatabase);
        $this->shodanApi = new ShodanApi($shodanApiKey);
    }

    /**
     * @return AccessLogInfoAggregationModel[]
     */
    public function collect(string $path, ProgressBar $progressBar): array
    {
        $data = $this->nginxReader->readLogFile($path);

        $progressBar->setMaxSteps(count($data));

        $result = [];
        foreach ($data as $item) {
            $result[] = new AccessLogInfoAggregationModel(
                $item,
                $this->ipDatabaseReader->country($item->getIp())->country->name,
                $this->shodanApi->getHostInfo($item->getIp()),
            );
            $progressBar->advance();
        }

        return $result;
    }
}