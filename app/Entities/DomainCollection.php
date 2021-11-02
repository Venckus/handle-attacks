<?php

namespace App\Entities;

use App\Entities\DomainEntity;

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
}