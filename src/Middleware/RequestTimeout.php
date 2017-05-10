<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/10
 * Time: 上午10:02
 */

namespace Sukui\Middleware;

use Sukui\ServerContext;

class RequestTimeout implements Middleware {

    public $timeout;
    public $exception;
    private $timerId = null;

    public function __construct($timeout,\Exception $ex=null) {
        $this->timeout = $timeout;
        if($ex === null){
            $this->exception = new \Exception("Reuest timeout",408);
        }else{
            $this->exception = $ex;
        }
    }

    public function __invoke(ServerContext $ctx, $next) {

        yield race([
            callCC(function($k){
                $this->timerId = swoole_timer_after($this->timeout, function() use ($k) {
                    $this->timerId = null;
                    $k(null, $this->exception);
                });
            }),
            function()use($next){
                yield $next;
                if($this->timerId){
                    swoole_timer_clear($this->timerId);
                }
            }
        ]);

    }
}