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

        $this->db_domains = Attack::all();
        
        $now = new \DateTime(date('h:i:s',strtotime('now')));

        $this->now = $now->format('h:i:s');
        
        return $this->process($path);
    }

    public function process($path)
    {
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
        // update DB domains modes
        $this->db_update();
    }

    public function check_domain($row_domain, $row_time)
    {
        if (isset($this->d->domains[$row_domain])) {

            if ($this->d->domains[$row_domain]['updated'] == $row_time) {

                // increase counter each time domain repeats
                $this->d->domains[$row_domain]['count'] += 1;

            } else $this->update_counters($row_domain, $row_time);

        } else $this->d->add($row_domain, $row_time);
    }

    public function update_counters($domain, $time)
    {
        // find time interval in seconds
        $interval = $this->time_diff($this->d->domains[$domain]['updated'], $time);

        if ($interval == 1
            && $this->d->domains[$domain]['count'] >= ENV('ATTACK_COUNT')) {

            $this->d->domains[$domain]['seconds'] += 1;
            $this->d->domains[$domain]['count'] = 0;
        }            
        if ($interval > ENV('ATTACK_TIME')
            && $this->d->domains[$domain]['seconds'] >= ENV('ATTACK_TIME')) {

            $this->d->domains[$domain]['attack_mode'] = 1;
        }
        $this->d->domains[$domain]['updated'] = $time;
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
        foreach ($this->d->domains as $domain) {

            if ($domain['attack_mode'] == 1) {

                Attack::where('domain', $domain)->update(['attack_mode' => 1]);

                if ($domain['rate_limiting'] == 1) {

                    Attack::where('domain', $domain)->update(['rate_limiting' => 1]);
                }
            }
        }
    }
}
