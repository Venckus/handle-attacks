<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Attack;

class AttackHandler
{

    private $mode;
    private $count;
    // private $domains;

    public function __construct()
    {
        $this->mode = 0;
        $this->count = 0;
        // $this->db_domains = Attack::all();
        return $this->process();
    }

    public function process()
    {
        $file = file('/var/log/nginx/ecu.de-access.log');
        // $db_domains = Attack::all();
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

        foreach ($data as $line => $row) {

            $tmp = explode(" ", $row);
            $time = substr($tmp[3],13); // attack time
            $log_domain = $tmp[2]; // domain name string
            // $interval = 0;
            $k = null;

            if ($line == 0) {
                // get first domain naim from database, if not found in database - create new record
                $db_domains[] = $this->db_get($log_domain);
                // Log::info(dump($this->domains));
                // create new domains array ([0] name, [1] latest request time, [2] request count )
                $domains[] = [$log_domain, $time, 0];
                continue;
            } else {
                // find matching domain name in saved domains arr
                foreach ($domains as $key => $dom) {
                    if ($dom[0] == $log_domain) {
                        $k = $key;
                        break;
                    }
                }
                // if domain names match
                if (isset($k)) {
                    // if time mach previous record
                    if ($domains[$k][1] == $time) $domains[$k][2] += 1; // increment attack count per second

                    $interval = $this->time_diff($domains[$k][1], $time);
                    // use ENV('ATTACK_COUNT') instead of 1
                    if ($interval > ENV('RATE_LIMIT_TIME')) {

                        if ($domains[$k][2] > $rate_bench) {
                            Attack::where('domain', $log_domain)->update(['rate_limiting' => 1]);
                        }
                        // use ENV('ATTACK_TIME') instead of 2 (150), write to db
                        if ( $domains[$k][2] > $attack_bench
                            && $interval > ENV('RATE_LIMIT_TIME')) {
                            Attack::where('domain', $log_domain)->update(['attack_mode' => 1]);
                        }
                    } else {
                        // when time do not match but name match, update arr time atribute
                        $domains[$k][1] = $time;
                    }
                } else {
                    // when domain not found, place new arrays row
                    $db_domains[] = $this->db_get($log_domain);
                    $domains[] = [ $log_domain, $time, 0 ];
                }
            }
        }
        dd($db_domains);
    }

    public function handle_domain( $domain )
    {
        $db_domain = Attack::where('domain', $domain)->get();
        dd($db_domain);
        if ($db_domain) {

            if ($db_domain->attack_mode == 0 && $db_domain->rate_limiting == 0)
                $db_domain->attack_mode = 1;

            elseif ($db_domain->attack_mode == 1 && $db_domain->rate_limiting == 0)
                $db_domain->rate_limiting = 1;
        } else {
            $new_attack = Attack::create([
                'domain' => $domain,
                'attack_mode' => 1,
            ]);
            $new_attack->save();
        }
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
