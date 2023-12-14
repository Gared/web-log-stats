<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Service;

use Gared\WebLogStats\Model\StatsModel;

class RankService
{
    /**
     * @param StatsModel[] $data
     * @return StatsModel[]
     */
    public function rank(array $data, int $count): array
    {
        usort($data, function (StatsModel $a, StatsModel $b) {
            return $a->getCount() < $b->getCount() ? 1 : -1;
        });

        return array_slice($data, 0, $count);
    }
}