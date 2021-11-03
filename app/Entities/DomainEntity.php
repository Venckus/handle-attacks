<?php

namespace App\Entities;

use DateTime;

class DomainEntity
{
    const REQUEST_COUNT_KEY = 'requestCount';
    public array $timestamps = [];
    public string $previousTimestamp;

    public function updateCount(DateTime $dateTime): void
    {
        $timestamp = $dateTime->getTimestamp();
        if (!array_key_exists($timestamp, $this->timestamps)) {
            $this->handleAttackModes();
            $this->timestamps[$timestamp][self::REQUEST_COUNT_KEY] = 1;
        } else {
            $this->timestamps[$timestamp][self::REQUEST_COUNT_KEY] += 1;
        }
    }
}