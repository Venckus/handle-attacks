<?php

namespace App\Handlers;

use App\Entities\DomainEntity;

class ModeHandler
{
    private ModeHandler $next;

    protected $isSequentialPeriods = true;

    public function setNext(ModeHandler $next): ModeHandler
    {
        $this->next = $next;
        return $next;
    }

    protected function setIsSequentialPeriods(bool $isSequentialPeriods): void
    {
        if ($this->isSequentialPeriods !== $isSequentialPeriods) {
            $this->isSequentialPeriods = $isSequentialPeriods;
        }
    }

    public function check(DomainEntity $domain, bool $isSequentialPeriods = true): bool
    {
        if (!$this->next) {
            return true;
        }
        return $this->check($domain);
    }

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
            // save domain mode to DB here.
        }
        return false;
    }
}