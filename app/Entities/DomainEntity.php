<?php

namespace App\Entities;

use DateTime;

class DomainEntity
{
    public array $timestamps = [];
    public string $previousTimestamp;

    public function updateCount(DateTime $dateTime): void
    {
        $timestamp = $dateTime->getTimestamp();
        if (!array_key_exists($timestamp, $this->timestamps)) {
            $this->handleAttackModes();
            // $this->setPreviousTimestamp($timestamp);
            // $this->updateLimiter();
            $this->timestamps[$timestamp] = [
                'requestCount' => 1,
                // 'attackMode' => false,
                // 'limitMode' => false,
            ];
        } else {
            $this->timestamps[$timestamp]['requestCount'] += 1;
        }
    }

    public function handleAttackModes()
    {
        // implement COR pattern here.
    }

    // public function updateLimiterModes()
    // {
    //     if (count($this->timestamps) > 1) {
    //         if (
    //             $this->timestamps[$this->previousTimestamp]['requestCount'] 
    //                 >= config('attack.mode.count')
    //         ) {
    //             $this->timestamps[$this->previousTimestamp]['attackMode'] = true;
    //         } elseif (
    //             $this->timestamps[$this->previousTimestamp]['requestCount'] 
    //                 >= config('attack.limitOn.count')
    //         ) {
    //             $this->timestamps[$this->previousTimestamp]['limitMode'] = true;
    //         }
    //     }
    // }

    // public function setPreviousTimestamp($timestamp)
    // {
    //     if (count($this->timestamps) > 1) {
    //         $this->previousTimestamp = array_key_last($this->timestamps);
    //     }
    // }
}