<?php

namespace App\Handlers;

use App\Entities\DomainEntity;
use App\Handlers\ModeHandlerInterface;

class ModeHandler implements ModeHandlerInterface
{
    
    /** @var ModeHandler */
    private ModeHandler $next;

    /** @var bool */
    protected bool $isSequentialPeriods = true;
    
    /** @var string */
    public string $attackLevel;

    /**
     * @param ModeHandlerInterface $next
     * 
     * @return ModeHandler $next
     */
    public function setNext(ModeHandlerInterface $next): ModeHandler
    {
        $this->next = $next;
        return $next;
    }

    /**
     * @return bool
     */
    public function getIstSequentialPeriods(): bool
    {
        return $this->isSequentialPeriods;
    }

    /**
     * @param bool $isSequentialPeriods
     * 
     * @return void
     */
    public function setIsSequentialPeriods(bool $isSequentialPeriods): void
    {
        if ($this->isSequentialPeriods !== $isSequentialPeriods) {
            $this->isSequentialPeriods = $isSequentialPeriods;
        }
    }

    /**
     * @param DomainEntity $domain,
     * @param bool $isSequentialPeriods
     * 
     * @return bool
     */
    public function check(DomainEntity $domain, bool $isSequentialPeriods = true): bool
    {
        if ($this->next) {
            return $this->next->check($domain, $isSequentialPeriods);
        }
        return false;
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
            if ($timestamp[DomainEntity::REQUEST_COUNT_KEY] >= $count) {
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

    /**
     * @return int
     */
    public function getAttackTimelimit(): int
    {
        return (int) config("attack.$this->attackLevel.time");
    }

    /**
     * @return int
     */
    public function getAttackCountlimit(): int
    {
        return (int) config("attack.$this->attackLevel.count");
    }
}