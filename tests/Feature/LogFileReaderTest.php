<?php

namespace Tests\Feature;

use App\Attack;
use App\Entities\AttackLevels;
use App\Entities\DomainEntity;
use App\Services\LogFileReader;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Entities\DomainCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogFileReaderTest extends TestCase
{
    use RefreshDatabase;

    const LOG_FILE_NAME = 'ecu.de-access.log';
    private LogFileReader $LogFileReader;

    protected function setUp(): void
    {
        $this->LogFileReader = new LogFileReader(self::LOG_FILE_NAME);
        parent::setUp();
    }

    public function testLogFileName(): void
    {
        $that = $this;
        $filename = self::LOG_FILE_NAME;
        $assertPropertyClosure = function () use ($that, $filename) {
            $that->assertStringContainsString($filename, $this->fileName);
        };
        $doAssertPropertyClosure = $assertPropertyClosure->bindTo(
            $this->LogFileReader,
            get_class($this->LogFileReader)
        );

        $this->assertObjectHasAttribute('fileName', $this->LogFileReader);
        $doAssertPropertyClosure();
    }

    // public function testFileHandler(): void
    // {
    //     $that = $this;
    //     $assertPropertyClosure = function () use ($that) {
    //         $that->assertEquals('resource', $this->fileHandler);
    //     };
    //     $doAssertPropertyClosure = $assertPropertyClosure->bindTo(
    //         $this->LogFileReader,
    //         get_class($this->LogFileReader)
    //     );
    //     $this->LogFileReader->loadFile();

    //     $doAssertPropertyClosure();
    // }

    /**
     * @dataProvider executeResultProvider
     */
    public function testExecute($fileName, $expectedResult, $expectedDomainsCount): void
    {
        $LogFileReader = new LogFileReader($fileName);
        $result = $LogFileReader->execute();

        $this->assertGreaterThanOrEqual(
            $expectedDomainsCount,
            count(array_keys($LogFileReader->getDomainCollection()->domainList))
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function executeResultProvider(): array
    {
        return [
            [
                'ecu.de-access.log',
                true,
                2,
            ],[
                'wrongLogFile.exe',
                false,
                0,
            ],
        ];
    }

    /**
     * @dataProvider saveResultsProvider
     */
    public function testSaveResults(
        array $domainNames, 
        bool $limitMode,
        string $attackLevel,
        int $expectedDomainCount
    ): void {
        foreach($domainNames as $domainName) {
            $domainCollection = new DomainCollection();
            $domainCollection->addDomain($domainName);
            $domainCollection->domainList[$domainName]->limitMode = $limitMode;
            $domainCollection->domainList[$domainName]->attackLevel = $attackLevel;
            // $this->LogFileReader
            //     ->domainCollection
            //     ->domainList[$domainName] = new DomainEntity();
            // $this->LogFileReader
            //     ->domainCollection
            //     ->domainList[$domainName]
            //     ->limitMode = true;
            // $this->LogFileReader
            //     ->domainCollection
            //     ->domainList[$domainName]
            //     ->attackLevel = AttackLevels::MODE_ON;
        }
        $this->LogFileReader->saveResults();
        // unset($domainName);
        foreach($domainNames as $domainName) {
            $this->assertEquals(
                $expectedDomainCount,
                Attack::where('domain', $domainName)->count()
            );

        }
    }

    public function saveResultsProvider(): array
    {
        return [
            [
                ['ecu.de'],
                true,
                AttackLevels::MODE_ON,
                1
            ],[
                ['ecu.de', 'ecu.co.uk'],
                true,
                AttackLevels::MODE_ON,
                2
            ]
        ];
    }
}