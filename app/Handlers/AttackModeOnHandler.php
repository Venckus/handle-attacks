<?php

namespace App\Handlers;

use App\Handlers\ModeHandler;
use App\Entities\DomainEntity;

class AttackModeOnHandler extends ModeHandler
{

    public function __construct(string $attackLevel)
    {
        $this->attackLevel = $attackLevel;
    }

    public function check(DomainEntity $domain, bool $isSequentialPeriods = true): bool
    {
        if (count($domain->timestamps) >= $this->getAttackTimelimit()) {
            $this->setIsSequentialPeriods($isSequentialPeriods);
            $result = $this->calculateMode(
                $domain,
                $this->getAttackCountlimit(),
                $this->getAttackTimelimit()
            );
            if ($result) {
                $domain->limitMode = true;
            }
            return $result;
        } else {
            return parent::check($domain, $isSequentialPeriods);
        }
    }
}