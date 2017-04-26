<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/4/25
 * Time: 下午2:40
 */
namespace Sukui;
class BufferChannel{
    public $cap;
    public $queue;
    public $recvCc;
    public $sendCc;
    public function __construct($cap) {
        $this->cap = $cap;
        $this->recvCc = new \SplQueue();
        $this->sendCc = new \SplQueue();
        $this->queue  = new \SplQueue();
    }

    public function send($val){
        return callCC(function($cc)use($val){


            if($this->cap > 0){
                $this->queue->enqueue($val);
                $this->cap--;
                $cc(null,null);
            }else{
                $this->sendCc->enqueue([$cc,$val]);
            }
            $this->sendPingPong();
        });
    }

    public function sendPingPong(){
        if(!$this->recvCc->isEmpty() && !$this->queue->isEmpty()){
            $recvCc = $this->recvCc->dequeue();
            $val = $this->queue->dequeue();
            $this->cap++;
            $recvCc($val);

            if(!$this->sendCc->isEmpty() && $this->cap >0){
                list($sendCc,$val) = $this->sendCc->dequeue();
                $this->queue->enqueue($val);
                $this->cap--;
                $sendCc(null,null);
                $this->sendPingPong();
            }
        }
    }

    public function recv(){
        return callCC(function($cc){
            if($this->sendCc->isEmpty()){
                $this->recvCc->enqueue($cc);
            }else{
                $val = $this->queue->dequeue();
                $this->cap++;
                $cc($val,null);
            }
            $this->recvPingPong();
        });
    }

    public function recvPingPong(){
        if(!$this->sendCc->isEmpty() && !$this->cap > 0){
            list($sendCc,$val) = $this->sendCc->dequeue();
            $this->queue->enqueue($val);
            $this->cap--;
            $sendCc(null,null);
            if(!$this->recvCc->isEmpty() && !$this->queue->isEmpty()){
                $recvCc = $this->recvCc->dequeue();
                $val = $this->queue->dequeue();
                $this->cap++;
                $recvCc($val,null);
                $this->recvPingPong();
            }
        }
    }
}