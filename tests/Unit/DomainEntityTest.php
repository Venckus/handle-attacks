<?php

namespace Tests\Unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use App\Entities\DomainEntity;

class DomainEntityTest extends TestCase
{
    public function testUpdateCount(): void
    {
        $DomainEntity = new DomainEntity();
        $dateTime = new DateTime('2021-10-30 12:09:01');
        $requests = 101;
        for ($i = 0; $i < $requests; $i++) {
            $DomainEntity->updateCount($dateTime);
        }
        $DomainEntity->updateCount(new DateTime('2021-10-30 12:09:02'));
        $this->assertEquals(
            $requests, 
            $DomainEntity->timestamps[$dateTime->getTimestamp()]['requestCount']
        );
        $this->assertEquals(2, count($DomainEntity->timestamps));
    }
}
