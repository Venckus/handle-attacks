<?php

namespace Tests\Unit;

use Tests\TestCase;
// use PHPUnit\Framework\TestCase;
use App\Handlers\ModeHandler;
use App\Entities\AttackLevels;
use App\Entities\DomainEntity;
use App\Handlers\AttackModeOnHandler;
use App\Handlers\AttackModeOffHandler;

class ModeHandlerTest extends TestCase
{
    /** @var AttackModeOnHandler */
    private AttackModeOnHandler $attackModeOnHandler;
    
    /** @var AttackModeOffHandler */
    private AttackModeOffHandler $attackLimitOffHandler;
    
    /** @var AttackModeOnHandler */
    private AttackModeOnHandler $attackLimitOnHandler;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->attackModeOnHandler = new AttackModeOnHandler(AttackLevels::MODE_ON);
        $this->attackLimitOnHandler = new AttackModeOnHandler(AttackLevels::LIMIT_ON);
        $this->attackLimitOffHandler = new AttackModeOffHandler(AttackLevels::LIMIT_OFF);
    }

    public function testGetConfigAttackNumbers(): void
    {
        $this->assertEquals(10, $this->attackModeOnHandler->getAttackTimelimit());
        $this->assertEquals(15, $this->attackModeOnHandler->getAttackCountlimit());

        $this->assertEquals(360, $this->attackLimitOnHandler->getAttackTimelimit());
        $this->assertEquals(20, $this->attackLimitOnHandler->getAttackCountlimit());

        $this->assertEquals(20, $this->attackLimitOffHandler->getAttackTimelimit());
        $this->assertEquals(8, $this->attackLimitOffHandler->getAttackCountlimit());
    }

    public function testSetIsSequentialPeriods(): void
    {
        $this->attackModeOnHandler->setIsSequentialPeriods(true);
        $this->attackLimitOnHandler->setIsSequentialPeriods(false);

        $this->assertTrue($this->attackModeOnHandler->getIstSequentialPeriods());
        $this->assertFalse($this->attackLimitOnHandler->getIstSequentialPeriods());
    }

    public function testCheckReturnsFalse(): void
    {
        $domain = new DomainEntity();
        $this->assertFalse($this->attackModeOnHandler->check($domain));
    }
}