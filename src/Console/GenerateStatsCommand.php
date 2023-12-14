<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Console;

use Gared\WebLogStats\Service\CollectDataService;
use Gared\WebLogStats\Service\GroupService;
use Gared\WebLogStats\Service\RankService;
use Gared\WebLogStats\Writer\JsonWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-stats',
    description: 'Generate stats from nginx logs'
)]
class GenerateStatsCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('log-file', 'l', InputOption::VALUE_REQUIRED, 'Path to log file');
        $this->addOption('stats-file', 's', InputOption::VALUE_REQUIRED, 'Path to stats output file');
        $this->addOption('shodan-api-key', null, InputOption::VALUE_REQUIRED, 'Shodan api key');
        $this->addOption('dbip', null, InputOption::VALUE_REQUIRED, 'Dbip database file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $collectDataService = new CollectDataService(
            $input->getOption('dbip'),
            $input->getOption('shodan-api-key'),
        );
        $data = $collectDataService->collect($input->getOption('log-file'));

        $grouper = new GroupService();
        $ranking = new RankService();

        $jsonData = [];

        $stats = $grouper->groupByOrg($data);
        $rankedStats = $ranking->rank($stats);
        foreach ($rankedStats as $item) {
            $jsonData['org'][] = [
                'count' => $item->getCount(),
                'name' => $item->getAccessLogInfoAggregationModel()->getHostInfo()->getOrg(),
            ];
        }

        $stats = $grouper->groupByCountry($data);
        $rankedStats = $ranking->rank($stats);
        foreach ($rankedStats as $item) {
            $jsonData['country'][] = [
                'count' => $item->getCount(),
                'name' => $item->getAccessLogInfoAggregationModel()->getHostInfo()->getCountryName(),
            ];
        }

        $stats = $grouper->groupByUserAgent($data);
        $rankedStats = $ranking->rank($stats);
        foreach ($rankedStats as $item) {
            $jsonData['user_agent'][] = [
                'count' => $item->getCount(),
                'name' => $item->getAccessLogInfoAggregationModel()->getLineModel()->getUserAgent(),
            ];
        }

        $writer = new JsonWriter();
        $writer->write($input->getOption('stats-file'), $jsonData);

        return self::SUCCESS;
    }
}