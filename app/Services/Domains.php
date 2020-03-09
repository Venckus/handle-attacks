<?php
namespace App\Services;

class Domains
{
    public $domains;

    public function __construct()
    {
    }
    public function add($name, $created)
    {
        $this->domains[$name] = [
            'count' => 0,
            'a_seconds' => 0,
            'attack_mode' => 0,
            'rate_limit_mode' => 0,
            'rate_seconds' => 0,
            'rate_off_seconds' => 0,
            'a_updated' => $created,
            'r_updated'=> $created
        ];
    }
}
