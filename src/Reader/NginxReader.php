<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Reader;

use Gared\WebLogStats\Model\AccessLogLineModel;
use RuntimeException;
use Symfony\Component\HttpFoundation\IpUtils;

class NginxReader
{
    private array $ipIgnoreCache = [];

    /**
     * @return AccessLogLineModel[]
     */
    public function readLogFile(string $path, ?array $ignoredIpRanges): array
    {
        $data = [];

        $logfiles = glob($path);
        if (count($logfiles) === 0) {
            throw new RuntimeException('No log files found in path: ' . $path);
        }

        foreach ($logfiles as $logfile) {
            $isGzip = str_ends_with($logfile, '.gz');

            $handle = $isGzip ? gzopen($logfile, 'r') : fopen($logfile, 'r');
            if ($handle === false) {
                throw new RuntimeException('Cannot open file: ' . $logfile);
            }

            $line = fgets($handle);
            while ($line !== false) {
                $info = $this->readLine($line);

                $isInRange = $this->ignoreIpRange($info->getIp(), $ignoredIpRanges);
                if ($isInRange === false) {
                    $data[$info->getIp().'-'.$info->getUserAgent()] = $info;
                }

                $line = fgets($handle);
            }

            $isGzip ? gzclose($handle) : fclose($handle);
        }

        return $data;
    }

    private function ignoreIpRange(string $ip, ?array $ignoredIpRanges): bool
    {
        if ($ignoredIpRanges === null) {
            return false;
        }

        if (array_key_exists($ip, $this->ipIgnoreCache)) {
            return $this->ipIgnoreCache[$ip];
        }

        $isInRange = IpUtils::checkIp($ip, $ignoredIpRanges);
        $this->ipIgnoreCache[$ip] = $isInRange;

        return $isInRange;
    }

    private function readLine(string $line): AccessLogLineModel
    {
        $lineData = preg_split('/[\s"\[\]]+/', $line);
        $ip = $lineData[0];
        $userAgent = $lineData[count($lineData) - 2];
        if ($userAgent === '-') {
            $userAgent = null;
        }

        return new AccessLogLineModel($ip, $userAgent);
    }
}