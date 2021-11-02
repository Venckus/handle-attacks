<?php

namespace App\Services;

use Generator;
use DateTime;
use App\Entities\DomainCollection;
use Illuminate\Support\Facades\Log;

class LogFileReader
{
    const STORAGE_PATH = '/storage/logs/';

    protected $fileName;
    protected $fileHandle;
    protected $domainCollection;

    public function __construct(string $fileName, bool $containsFullPath = null)
    {
        if ($containsFullPath) {
            $this->fileName = $fileName;
        } else {
            $this->fileName = getcwd() . self::STORAGE_PATH . $fileName;
        }
        $this->domainCollection = new DomainCollection();
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getDomainCollection(): DomainCollection
    {
        return $this->domainCollection;
    }

    public function execute(): ?string
    {
        $result = null;
        if ($this->loadFile()) {
            try {
                $this->processLines();
                $result = 'processed';
            } catch (\Excepton $e) {
                Log::error($e);
                $result = json_encode($e);
            }
        }
        return $result;
    }

    public function processLines(): void
    {
        $lineGenerator = $this->getOneLine();
        foreach($lineGenerator as $lineData) {
            list($dateTime, $domainName) = $this->getDatetimeAndDomain($lineData);
            $this->domainCollection->addDomain($domainName);
            $this->domainCollection->domainList[$domainName]->updateCount($dateTime);
        }
    }

    public function loadFile(): bool
    {
        if (empty($this->fileHandle) && file_exists($this->fileName)) {
            $this->fileHandle = popen("tac $this->fileName", "r");
            return true;
        } else {
            // Log::error("Could not open file: $this->fileName");
            return false;
        }
    }

    public function getOneLine(): Generator
    {
        while ($line = fgets($this->fileHandle)) {
            yield $line;
        }
    }

    public function getDatetimeAndDomain(string $line): array
    {
        $logLineArr = explode(" ", $line);
        $domainName = $logLineArr[2];
        $date = substr($logLineArr[3], 1, 11);
        $time = substr($logLineArr[3], 13, 19);
        $date = str_replace("/", "-", $date);
        $dateTime = new DateTime("$date $time");
        return [$dateTime, $domainName];
    }
}