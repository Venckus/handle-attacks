<?php

namespace App\Entities;

use DateTime;

class DomainEntity
{
    const REQUEST_COUNT_KEY = 'requestCount';

    /** @var array */
    public array $timestamps = [];

    /** @var string */
    public string $previousTimestamp;
    
    /** @var string */
    public string $attackLevel;

    /** @var bool */
    public bool $limitMode = false;

    /**
     * @param DateTime $dateTime
     * 
     * @return void
     */
    public function updateCount(DateTime $dateTime): void
    {
        $timestamp = $dateTime->getTimestamp();
        if (!array_key_exists($timestamp, $this->timestamps)) {
            $this->timestamps[$timestamp][self::REQUEST_COUNT_KEY] = 1;
        } else {
            $this->timestamps[$timestamp][self::REQUEST_COUNT_KEY] += 1;
        }
    }
}
