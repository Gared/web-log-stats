<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Model;

class StatsModel
{
    public function __construct(
        private readonly AccessLogInfoAggregationModel $infoModel,
        private int $count = 1,
    ) {
    }

    public function incrementCount(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getAccessLogInfoAggregationModel(): AccessLogInfoAggregationModel
    {
        return $this->infoModel;
    }
}