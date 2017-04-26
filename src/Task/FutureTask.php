<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/4/25
 * Time: 下午4:35
 */

namespace Sukui\Task;

final class FutureTask{
    const PENDING = 1;
    const DONE = 2;
    const TIMEOUT = 3;
    private $timerId;
    private $cc;

    public $state;
    public $result;
    public $ex;
    public function __construct(\Generator $gen,AsyncTask $parent = null) {

        $this->state = self::PENDING;

        if($parent){
            $asyncTask = new AsyncTask($gen,$parent);
        }else{
            $asyncTask = new AsyncTask($gen);
        }

        $asyncTask->begin(function($r,$ex=null){

            if($this->state === self::TIMEOUT){
                return ;
            }

            $this->state = self::DONE;
            if($cc = $this->cc){
                if($this->timerId){
                    swoole_timer_clear($this->timerId);
                }
                $cc($r,$ex);
            }else{
                $this->result = $r;
                $this->ex = $ex;
            }
        });
    }

    public function get($timeout=0){
        return callCC(function($cc)use($timeout){
            if($this->state === self::DONE){
                $cc($this->result,$this->ex);
            }else{
                $this->cc = $cc;
                $this->getResukltTimeout($timeout);
            }
        });
    }

    private function getResukltTimeout($timeout){
        if(!$timeout){
            return ;
        }
        $this->timerId = swoole_timer_after($timeout, function() {
            $this->state = self::TIMEOUT;
            $cc = $this->cc;
            $cc(null, new \Exception("timeout"));
        });
    }
}