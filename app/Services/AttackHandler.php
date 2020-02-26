<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Services\Domains;
use App\Attack;

class AttackHandler
{

    // private $mode;
    // private $count;
    private $d;

    public function __construct()
    {
        $this->d = new Domains;
        // $this->mode = 0;
        // $this->count = 0;
        // $this->db_domains = Attack::all();
        return $this->process();
    }

    public function process()
    {
        $file = file('/var/log/nginx/ecu.de-access.log');

        $result = $this->filter_domains($file);

        if ($result) return true;

        else return false;
    }

    /**
     *
     * @return mixed
     */
    public function filter_domains( $data )
    {
        $domains = [];
        $rate_bench = ENV('RATE_LIMIT_TIME') * ENV('RATE_LIMIT_COUNT');
        $attack_bench = ENV('ATTACK_TIME') * ENV('ATTACK_COUNT');
        $rate_of_bench = ENV('RATE_LIMIT_OF_TIME') * ENV('RATE_LIMIT_OF_COUNT');

        foreach ($data as $line => $row) {

            $tmp = explode(" ", $row);
            $row_time = substr($tmp[3],13); // attack time
            $row_domain = $tmp[2]; // domain name string

            if ($line == 0) {
                // create new domains object array
                $this->d->add($row_domain, $row_time);
                continue;
            } else {
                // find matching domain name in temp object domains
                if (isset($this->d->domains[$row_domain])) {
                    // increase counter each time domain repeats
                    $this->d->domains[$row_domain]['count'] += 1;
                    // when counter reach rate limit count benchmark
                    if ($this->d->domains[$row_domain]['count'] >= $rate_bench
                        && $this->d->domains[$row_domain]['rate_limiting'] == 0) {
                        // find time interval in seconds
                        $interval = $this->time_diff($this->d->domains[$row_domain]['created'], $row_time);
                        //when time interval is less or equal to rate limit time limit
                        if ($interval <= ENV('RATE_LIMIT_TIME')) {
                            // update time
                            $this->d->domains[$row_domain]['updated'] = $row_time;
                            // set database to rate limiting mode 'on'
                            Attack::where('domain', $row_domain)->update(['rate_limiting' => 1]);
                        }
                    // when counter reach attack mode count benchmark
                    } elseif ($this->d->domains[$row_domain]['count'] >= $attack_bench
                        && $this->d->domains[$row_domain]['attack_mode'] == 0) {
                        // find time interval in seconds
                        $interval = $this->time_diff($this->d->domains[$row_domain]['created'], $row_time);
                        // when time intervall is less or equal to attack mode time limit
                        if ($interval <= ENV('ATTACK_TIME')) {
                            // update time
                            $this->d->domains[$row_domain]['updated'] = $row_time;
                            // set attack mode on for domains object
                            $this->d->domains[$row_domain]['attack_mode'] = 1;
                            // set attack mode in database
                            Attack::where('domain', $row_domain)->update(['attack_mode' => 1]);
                        } else {
                            // when benchmark is not reached, reset time and count and set rate limiting off
                            $this->reset_arr($row_domain, $row_time);
                            $this->d->domains[$row_domain]['rate_limiting'] = 0;
                        }
                    // turn off rate limiting when count is below benchmark
                    } elseif ($this->d->domains[$row_domain]['count'] <= $rate_of_bench) {
                        // find time interval in seconds
                        $interval = $this->time_diff($this->d->domains[$row_domain]['created'], $row_time);
                        // when interval is bigger than rate limit benchmark
                        if ($interval >= ENV('RATE_LIMIT_OF_TIME')) {
                            // set database to rate limiting mode 'on'
                            Attack::where('domain', $row_domain)->update(['rate_limiting' => 0]);
                            // reset object domain time and count
                            $this->reset_arr($row_domain, $row_time);
                            $this->d->domains[$row_domain]['rate_limiting'] = 0;
                        }
                    }
                // when new log file record domain is not in object domains
                } else {
                    $this->d->add($row_domain, $row_time); // add new domain to object array
                }
            }
        }
        // dd($db_domains);
    }

    public function reset_arr( $domain, $time )
    {
        $this->d->domains[$domain]['created'] = $time;
        $this->d->domains[$domain]['updated'] = $time;
        $this->d->domains[$domain]['count'] = 0;
    }
    /**
     * counts interval in seconds
     * @return int
     */
    public function time_diff($a , $b)
    {
        $timeA = new \DateTime(date('h:i:s',strtotime($a)));
        $timeB = new \DateTime(date('h:i:s',strtotime($b)));
        $interval = $timeB->diff($timeA);

        $seconds  = $interval->i * 60;
        $seconds += $interval->s;
        return $seconds;
    }

    private function db_get($name)
    {
        try {
            $db_domain = Attack::firstOrCreate(['domain' => $name]);
        } catch (\Exception $e) {
            dd($e);
        }
        return $db_domain;
    }
}
