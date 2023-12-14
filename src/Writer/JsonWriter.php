<?php
declare(strict_types=1);

namespace Gared\WebLogStats\Writer;

use RuntimeException;

class JsonWriter
{
    /**
     * @param array<string, array<int, array<string, int|string|null>>> $jsonData
     */
    public function write(string $path, array $jsonData): void
    {
        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new RuntimeException('Cannot open file: ' . $path);
        }

        fwrite($handle, json_encode($jsonData));
        fclose($handle);
    }
}