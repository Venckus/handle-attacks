<?php

/*
 *
 * attack constants from .env
 * 
 */
return [
    'mode' => [
        'time' => ENV('ATTACK_TIME', 10),
        'count' => ENV('ATTACK_COUNT', 15),
    ],
    'rate_on' => [
        'time' => ENV('RATE_LIMIT_TIME', 360),
        'count' => ENV('RATE_LIMIT_COUNT', 20),
    ],
    'rate_off' => [
        'time' => ENV('RATE_LIMIT_TIME', 20),
        'count' => ENV('RATE_LIMIT_COUNT', 8),
    ]
];