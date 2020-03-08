<?php
namespace App\Services;

class LogFile
{
    protected $file;
    protected $rew;

    public function __construct($filename, $mode = "r")
    {
        if (!file_exists($filename)) {

            throw new \Exception("File not found");
        }
        // $this->rew 
        $rews = popen("tac $filename",'r');
        while ($line = fgets($rews)) {

            dd($line);
         }
        $this->file = new \SplFileObject($filename, $mode);
    }

    protected function iterateText()
    {
        $count = 0;

        while (!$this->file->eof()) {

            yield $this->file->fgets();

            $count++;
        }
        return $count;
    }

    public function iterate() //$type = "Text", $bytes = NULL)
    {
        return new \NoRewindIterator($this->iterateText());
    }

    public function backwards()//$date)
    {
        $c = 0;
        $res = [];
        // while ($date <= $f_date) {
        while ($c > -3) {
            $res[] = $this->file->fseek($c,SEEK_END);
            $c--;
        }
        return $res;
    }
}