<?php

/*
 *
 * attack modes constants from .env
 * 
 */
return [
    'modeOn' => [
        'time' => ENV('ATTACK_TIME', 10),
        'count' => ENV('ATTACK_COUNT', 15),
    ],
    'limitOn' => [
        'time' => ENV('RATE_LIMIT_TIME', 360),
        'count' => ENV('RATE_LIMIT_COUNT', 20),
    ],
    'limitOff' => [
        'time' => ENV('RATE_LIMIT_TIME', 20),
        'count' => ENV('RATE_LIMIT_COUNT', 8),
    ]
];