<?php

namespace App\Services;

use DateTime;
use Generator;
use App\Attack;
use DateInterval;
use App\Entities\AttackLevels;
use App\Entities\DomainCollection;
use Illuminate\Support\Facades\Log;

class LogFileReader
{
    const STORAGE_PATH = '/storage/logs/';
    const LOGFILE_TIME_PERIOD = 'PT5M';

    /** @var string */
    protected string $fileName;

    /** @var resource $fileHandle TODO what is this type? resource|stream */
    protected $fileHandle;

    /** @var DomainCollection */
    protected DomainCollection $domainCollection;

    /** @var DateTime */
    protected DateTime $readUntilDateTime;


    /**
     * @param string $fileName
     * @param bool $containsFullPath
     * 
     * @return void
     */
    public function __construct(string $fileName, bool $containsFullPath = null)
    {
        $this->setFileName($fileName, $containsFullPath);
        $this->domainCollection = new DomainCollection();
        $this->readUntilDateTime = new DateTime();
        $this->readUntilDateTime->sub(new DateInterval(self::LOGFILE_TIME_PERIOD));
    }

    /**
     * @param string $fileName
     * @param bool $containsFullPath
     * 
     * @return void
     */
    protected function setFileName(
        string $fileName, 
        bool $containsFullPath = null
    ): void {
        if ($containsFullPath) {
            $this->fileName = $fileName;
        } else {
            $this->fileName = getcwd() . self::STORAGE_PATH . $fileName;
        }
    }

    /**
     * @return DomainCollection
     */
    public function getDomainCollection(): DomainCollection
    {
        return $this->domainCollection;
    }

    /**
     * @return DomainCollection
     */
    public function setDomainCollection(DomainCollection $domainCollection): void
    {
        $this->domainCollection = $domainCollection;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $result = false;
        if ($this->loadFile()) {
            try {
                $this->processLines();
                $this->saveResults();
                $result = true;
            } catch (\Exception $e) {
                Log::error($e);
            }
        }
        return $result;
    }

    /**
     * @return void
     */
    public function processLines(): void
    {
        $lineGenerator = $this->readOneLine();
        foreach($lineGenerator as $lineData) {
            list($dateTime, $domainName) = $this->extractDatetimeAndDomain($lineData);
            if ($this->readUntilDateTime >= $dateTime) {
                pclose($this->fileHandle);
                return;
            } 
            $this->domainCollection->addDomain($domainName);
            $this->domainCollection->domainList[$domainName]->updateCount($dateTime);
        }
    }

    /**
     * @return void
     */
    public function saveResults(): void
    {
        $domainRecords = [];
        foreach($this->domainCollection->domainList as $domainName => $domain) {
            $domainRecords[] = [
                Attack::DOMAIN_FIELD_NAME => $domainName,
                Attack::ATTACK_MODE_FIELD_NAME => (
                    $domain->limitMode 
                    && $domain->attackLevel === AttackLevels::MODE_ON
                ),
                Attack::RATE_LIMITING_FIELD_NAME => (
                    $domain->limitMode 
                    && $domain->attackLevel === AttackLevels::LIMIT_ON
                )
            ];
        }
        Attack::upsert($domainRecords);
    }

    /**
     * @return bool
     */
    public function loadFile(): bool
    {
        if (empty($this->fileHandle) && file_exists($this->fileName)) {
            $this->fileHandle = popen("tac $this->fileName", "r");
            return true;
        }
        return false;
    }

    /**
     * @return Generator
     */
    public function readOneLine(): Generator
    {
        while ($line = fgets($this->fileHandle)) {
            yield $line;
        }
    }

    /**
     * @param string $line
     * 
     * @return array
     */
    public function extractDatetimeAndDomain(string $line): array
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
