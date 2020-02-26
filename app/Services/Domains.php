<?php
namespace App\Services;

class Domains
{
    // private $name;
    // private $count;
    // private $created;
    // private $updated;
    public $domains;

    public function __construct()//$domains) //$name, $created)
    {
        // $this->domains[] = $domains;
        // $this->name = $name;
        // $this->created = $created;
        // $this->created = $created;
        // $this->count = 0;
    }
    public function add($name, $created)
    {
        // $this->domains[$name] = $domain;
        $this->domains[$name] = [
            'count' => 0,
            // 'seconds' => 0,
            'attack_mode' => 0,
            'rate_limiting' => 0,
            'created' => $created,
            'updated'=> $created
        ];
    }
}
