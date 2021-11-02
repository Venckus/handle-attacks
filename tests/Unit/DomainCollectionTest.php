<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Entities\DomainCollection;
use App\Entities\DomainEntity;

class DomainCollectionTest extends TestCase
{
    public function testAddDomain()
    {
        $DomainCollection = new DomainCollection();
        $generator = $this->domainNameProvider();
        foreach($generator as $items) {
            list($domainName, $expectedDomainsCount) = $items;
            $DomainCollection->addDomain($domainName);
            $this->assertInstanceOf(DomainEntity::class, $DomainCollection->domainList[$domainName]);
            $this->assertEquals($expectedDomainsCount, count($DomainCollection->domainList));
        }
    }

    public function domainNameProvider()
    {
        $arr = [
            ['bedu.edu', 1],
            ['one.more', 2],
            ['bedu.edu', 2],
        ];
        foreach($arr as $element) {
            yield $element;
        }
    }
}