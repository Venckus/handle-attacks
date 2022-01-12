<?php

namespace App\Handlers;

use App\Handlers\ModeHandler;
use App\Entities\DomainEntity;

class AttackModeOffHandler extends ModeHandler
{
    /**
     * @param string $attackLevel
     */
    public function __construct(string $attackLevel)
    {
        $this->attackLevel = $attackLevel;
    }

    /**
     * @param DomainEntity $domain
     * @param bool $isSequentialPeriods
     * 
     * @return bool
     */
    public function check(DomainEntity $domain, bool $isSequentialPeriods = true) : bool
    {
        if (
            count($domain->timestamps) 
            >= $this->getAttackTimelimit()
        ) {
            $this->setIsSequentialPeriods($isSequentialPeriods);
            $result = $this->calculateMode(
                $domain,
                $this->getAttackCountlimit(),
                $this->getAttackTimelimit()
            );
            if ($result) {
                $domain->limitMode = true;
                $domain->attackLevel = $this->attackLevel;
            }
            return $result;
        } else {
            return parent::check($domain, $isSequentialPeriods);
        }
    }

    /**
     * @param DomainEntity $domain
     * @param int $count
     * @param int $time
     * 
     * @return bool
     */
    public function calculateMode(DomainEntity $domain, int $count, int $time): bool
    {
        $periodCount = 0;
        $lastPeriodMode = true;

        foreach ($domain->timestamps as $timestamp) {
            if ($timestamp[DomainEntity::REQUEST_COUNT_KEY] <= $count) {
                if ($this->isSequentialPeriods) {
                    if ($lastPeriodMode) {
                        $periodCount++;
                    } else {
                        $periodCount = 0;
                    }
                } else {
                    $periodCount++;
                }
                $lastPeriodMode = true;
            } else {
                $lastPeriodMode = false;
            }
        }

        if ($periodCount >= $time) {
            return true;
        }
        return false;
    }
}
