<?php

namespace App\Entities;

use App\Entities\DomainEntity;
use App\Handlers\ModeHandler;
use App\Handlers\AttackModeOnHandler;
use App\Handlers\AttackModeOffHandler;

class DomainCollection
{
    /** @var array[DomainEntity] */
    public array $domainList = [];

    /**
     * @param string $domainName
     * 
     * @return void
     */
    public function addDomain(string $domainName) : void
    {
        if (array_key_exists($domainName, $this->domainList)) {
            return;
        } else {
            $this->domainList[$domainName] = new DomainEntity();
        }
    }

    /**
     * @return void
     */
    public function calculateModes(): void
    {
        foreach ($this->domainList as $domain) {
            if (count($domain->timestamps) >= config('attack.modeOn.count')) {
                $modeHandler = new AttackModeOnHandler(AttackLevels::LIMIT_ON);
                $modeHandler
                    ->setNext(new AttackModeOnHandler(AttackLevels::MODE_ON))
                    ->setNext(new AttackModeOffHandler(AttackLevels::LIMIT_OFF));
                $modeHandler->check($domain);
            }
        }
    }
}
