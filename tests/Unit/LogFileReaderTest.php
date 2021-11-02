<?php

namespace Tests\Unit;

use DateTime;
use App\Services\LogFileReader;
use PHPUnit\Framework\TestCase;

class LogFileReaderTest extends TestCase
{
    public function testFilePathIsCorrect()
    {
        $LogFileReader = new LogFileReader('ecu.de-access.log');
        $filePath = '/var/www/storage/logs/ecu.de-access.log';
        $this->assertEquals($filePath, $LogFileReader->getFileName());
    }


    /**
     * @dataProvider oneLineProvider
     */
    public function testGetDatetimeAndDomain($line, $dateTimeExpected, $domainExpected)
    {
        $LogFileReader = new LogFileReader('ecu.de-access.log');
        list($dateTime, $domainName) = $LogFileReader->getDatetimeAndDomain($line);
        $this->assertEquals($domainExpected, $domainName);
        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertEquals($dateTimeExpected, $dateTime->format('Y-m-d H:i:s'));
    }

    public function oneLineProvider()
    {
        return [
            [
                '172.68.226.48 - ecu.hu [11/Jan/2019:13:36:59 +0000] "GET /mercedes/motorvez%C3%A9rl%C5%91-egys%C3%A9g/edc15c0-(cdi1)-3261 HTTP/1.1" 200 216370 "https://ecu.hu/mercedes/motorvez%C3%A9rl%C5%91-egys%C3%A9g-40" "Mozilla/5.0 (Linux; Android 7.0; HUAWEI VNS-L21) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.99 Mobile Safari/537.36"',
                '2019-01-11 13:36:59',
                'ecu.hu'
            ],
            [
                '162.158.111.37 - ecu.co.uk [11/Jan/2019:13:36:01 +0000] "GET /peugeot/calculateur-moteur/edc16c39-4114 HTTP/1.1" 200 197379 "https://ecu.co.uk/peugeot/calculateur-moteur-52" "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"',
                '2019-01-11 13:36:01',
                'ecu.co.uk'
            ]
        ];
    }

    public function testLoadFile()
    {
        $LogFileReader = new LogFileReader('ecu.de-access.log');
        $LogFileReader->loadFile();
        $line = $LogFileReader->getOneLine();
        $this->assertNotEmpty($line);
        $this->assertInstanceOf(\Generator::class, $line);
    }

    /**
     * @dataProvider executeResultProvider
     */
    public function testExecute($fileName, $expectedResult, $expectedDomainsCount)
    {
        $LogFileReader = new LogFileReader($fileName);
        $result = $LogFileReader->execute();
        $domainCollection = $LogFileReader->getDomainCollection();
        $this->assertGreaterThanOrEqual(
            $expectedDomainsCount,
            count(array_keys($LogFileReader->getDomainCollection()->domainList))
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function executeResultProvider()
    {
        return [
            [
                'ecu.de-access.log',
                'processed',
                4,
            ],
            [
                'wrongLogFile.exe',
                null,
                0
            ]
        ];
    }
}
