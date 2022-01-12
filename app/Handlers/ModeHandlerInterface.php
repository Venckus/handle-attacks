<?php

namespace App\Handlers;

use App\Entities\DomainEntity;

interface ModeHandlerInterface
{
    public function setNext(ModeHandlerInterface $handler): ModeHandler;

    public function check(DomainEntity $domain, bool $isSequentialPeriods): bool;
}