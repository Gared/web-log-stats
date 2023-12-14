<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Reader;

use Gared\WebLogStats\Model\AccessLogLineModel;
use RuntimeException;

class NginxReader
{
    /**
     * @return AccessLogLineModel[]
     */
    public function readLogFile(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Cannot open file: ' . $path);
        }

        $data = [];

        $line = fgets($handle);
        while ($line !== false) {
            $data[] = $this->readLine($line);
            $line = fgets($handle);
        }

        fclose($handle);

        return $data;
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