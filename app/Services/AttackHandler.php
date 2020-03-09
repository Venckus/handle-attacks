<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Services\Domains;
use App\Services\LogFile;
use App\Attack;

class AttackHandler
{

    // private $mode;
    // private $count;
    private $d, $db_domains, $now;

    public function __construct($path)
    {
        $this->d = new Domains;
        
        // uncomment this for actual 'now' time
        // $now = new \DateTime(date('h:i:s',strtotime('now')));
        // this is only for testing purposes
        $this->now = "13:36:59"; // $now->format('h:i:s');
        
        return $this->process($path);
    }

    public function process($path)
    {
        // get all domains with attack mode on from DB
        $this->select_domains();
        // read file from the end
        $reversed = popen("tac $path","r");

        // read lines one by one 
        while ($line = fgets($reversed)) {

            $tmp = explode(" ", $line); // get time and domain name array
            $row_domain = $tmp[2]; // domain name string
            $row_time = substr($tmp[3],13); // attack time

             // when log file time goes back more than 6 minutes
            if ($this->time_diff($this->now, $row_time) > 360) {

                break; // stop reading file

            } else $this->check_domain($row_domain, $row_time);
        }
        pclose($reversed);
        // update DB domains modes
        if ($this->d->domains != []) $this->db_update();
    }

    public function select_domains()
    {
        $this->db_domains = Attack::where('attack_mode', 1)->get();
    }

    public function check_domain($row_domain, $row_time)
    {
        if (isset($this->d->domains[$row_domain])) {

            if ($this->d->domains[$row_domain]['a_updated'] == $row_time) {

                // increase counter each time domain repeats
                $this->d->domains[$row_domain]['count'] += 1;

            } else $this->update_counters($row_domain, $row_time);

        } else $this->d->add($row_domain, $row_time);
    }

    public function update_counters($domain, $row_time)
    {
        // when attack mode is off
        if ($this->d->domains[$domain]['attack_mode'] == 0) {
            
            $this->check_attack_mode($domain, $row_time);

        // when attack mode is on
        } else {
            $this->check_rate_limit($domain, $row_time);
        }
        $this->d->domains[$domain]['count'] = 0;
    }

    public function check_rate_limit($domain, $row_time)
    {
        $interval = $this->time_diff($this->d->domains[$domain]['r_updated'], $row_time);

        if ($interval == 1) {

            if ($this->d->domains[$domain]['count'] >= (int)config('attack.rate.count')) {

                $this->d->domains[$domain]['rate_seconds'] += 1;
                $this->d->domains[$domain]['r_updated'] = $row_time;

            } elseif ($this->d->domains[$domain]['count'] < (int)config('attack.rate_off.count')) {

                $this->d->domains[$domain]['rate_off_seconds'] += 1;
            }

        } elseif ($this->d->domains[$domain]['rate_limit_mode'] == 1
                && $this->d->domains[$domain]['rate_off_seconds'] >= (int)config('attack.rate_off.time')) {

            $this->d->domains[$domain]['rate_limit_mode'] = 0;
        }
        
    }

    public function check_attack_mode($domain, $row_time)
    {
        $interval = $this->time_diff($this->d->domains[$domain]['a_updated'], $row_time);

        if ($interval == 1
        && $this->d->domains[$domain]['count'] >= (int)config('attack.mode.count')) {
        
        $this->d->domains[$domain]['a_seconds'] += 1; dump("$domain adding seconds:");dump($this->d->domains[$domain]);  
        }            
        if ($this->d->domains[$domain]['a_seconds'] >= (int)config('attack.mode.time')) {

            $this->d->domains[$domain]['attack_mode'] = 1; dump("$domain ATTACK MODE ON");dump($this->d->domains[$domain]);
        }
    }

    /**
     * counts interval in seconds
     * @return int
     */
    public function time_diff($a, $b)
    {
        $timeA = new \DateTime(date('h:i:s',strtotime($a)));
        $timeB = new \DateTime(date('h:i:s',strtotime($b)));
        $interval = $timeB->diff($timeA);

        $seconds  = $interval->i * 60;
        $seconds += $interval->s;
        return $seconds;
    }

    private function db_update()
    {
        foreach ($this->d->domains as $domain => $v) {
            
            if ($v['attack_mode'] == 1 && $this->db_domains->domain == $domain) {
                
                $db_domain = Attack::where('domain', $domain)->get();
                
                if ($db_domain->check_attack_mode == 0) {
                    $db_domain->check_attack_mode = 1;
                    $db_domain->save();
                }
                // Attack::where('domain', $domain)->update(['attack_mode' => 1]);

                if ($v['rate_limit_mode'] == 1) {

                    Attack::where('domain', $domain)->update(['rate_limiting' => 1]);
                }
            }
        }
    }
}
