<?php

namespace App\Entities;

use App\Entities\DomainEntity;
use App\Handlers\ModeHandler;
use App\Handlers\AttackModeOnHandler;
use App\Handlers\AttackModeOffHandler;

class DomainCollection
{
    public $domainList = [];

    public function addDomain(string $domainName) : void
    {
        if (array_key_exists($domainName, $this->domainList)) {
            return;
        } else {
            $this->domainList[$domainName] = new DomainEntity();
        }
    }

    public function calculateModes()
    {
        foreach ($this->domainList as $domain) {
            if (count($domain->timestamps) >= config('attack.modeOn.count')) {
                $modeHandler = new AttackModeOnHandler('limitOn');
                $modeHandler->setNext(new AttackModeHandler('modeOn'))
                    ->setNext(new AttackModeOffHandler('limitOff'));
                $result = $modeHandler->check($domain);
            }
        }
    }
}