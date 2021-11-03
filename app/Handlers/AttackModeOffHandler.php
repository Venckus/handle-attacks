<?php

namespace App\Handlers;

use App\Handlers\ModeHandler;
use App\Entities\DomainEntity;

class AttackModeOnHandler extends ModeHandler
{
    private string $attackLevel = '';

    public function __construct(string $attackLevel)
    {
        $this->attackLevel = $attackLevel;
    }

    public function check(DomainEntity $domain, bool $isSequentialPeriods = true) : bool
    {
        if (count($domain->timestamps) >= config('attack.' . $this->attackLevel . '.time')) {
            $this->setIsSequentialPeriods($isSequentialPeriods);
            $result = parent::calculateMode(
                $domain,
                (int) config('attack.' . $this->attackLevel . '.count'),
                (int) config('attack.' . $this->attackLevel . '.time')
            );
            if ($result) {
                $domain->limitMode = true;
                // save domain mode to DB here.
            }
            return $result;
        } else {
            return parent::check($domain);
        }
    }

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