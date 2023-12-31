<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Service;

use Gared\WebLogStats\Model\AccessLogInfoAggregationModel;
use Gared\WebLogStats\Model\StatsModel;

class GroupService
{
    /**
     * @param AccessLogInfoAggregationModel[] $data
     * @return StatsModel[]
     */
    public function groupByUserAgent(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $key = $item->getLineModel()->getUserAgent();
            if (array_key_exists($key, $result)) {
                $result[$key]->incrementCount();
            } else {
                $result[$key] = new StatsModel($item);
            }
        }

        return $result;
    }

    /**
     * @param AccessLogInfoAggregationModel[] $data
     * @return StatsModel[]
     */
    public function groupByCountry(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $key = $item->getHostInfo()?->getCountryName() ?? $item->getCountryName();
            if (array_key_exists($key, $result)) {
                $result[$key]->incrementCount();
            } else {
                $result[$key] = new StatsModel($item);
            }
        }

        return $result;
    }

    /**
     * @param AccessLogInfoAggregationModel[] $data
     * @return StatsModel[]
     */
    public function groupByOrg(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $key = $item->getHostInfo()?->getOrg();
            if (array_key_exists($key, $result)) {
                $result[$key]->incrementCount();
            } else {
                $result[$key] = new StatsModel($item);
            }
        }

        return $result;
    }

    /**
     * @param AccessLogInfoAggregationModel[] $data
     * @return StatsModel[]
     */
    public function groupByIsp(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $key = $item->getHostInfo()?->getIsp();
            if (array_key_exists($key, $result)) {
                $result[$key]->incrementCount();
            } else {
                $result[$key] = new StatsModel($item);
            }
        }

        return $result;
    }
}