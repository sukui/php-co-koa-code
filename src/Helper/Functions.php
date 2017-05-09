<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/4/1
 * Time: 上午10:35
 */

use Sukui\ServerContext;
use Sukui\SysCall;
use Sukui\Channel;
use Sukui\BufferChannel;
use Sukui\Task\Any;
use Sukui\Task\Async;
use Sukui\Task\AsyncTask;
use Sukui\Task\CallCC;
use Sukui\Task\All;
use Sukui\Task\FutureTask;

function spawn(){
    $n = func_num_args();
    if($n === 0){
        return;
    }
    $task = func_get_arg(0);
    $continuation = function(){};
    $paren = null;
    $ctx = [];

    for ($i=1;$i<$n;$i++){
        $arg = func_get_arg($i);
        if(is_callable($arg)){
            $continuation = $arg;
        }elseif($arg instanceof Async){
            $paren = $arg;
        }elseif(is_array($arg)){
            $ctx = $arg;
        }
    }

    if(is_callable($task)){
        try{
            $task = $task();
        }catch (\Exception $ex){
            $continuation(null,$ex);
            return;
        }
    }

    if($task instanceof \Generator){
        foreach ($ctx as $k => $v){
            $task->k = $v;
        }
        (new AsyncTask($task,$paren))->begin($continuation);
    }else{
        $continuation($task,null);
    }
}


function callCC(callable $fun,$timeout=0){
    if($timeout>0){
        $fun = timeoutWrapper($fun,$timeout);
    }
    return new CallCC($fun);
}

function async_sleep($ms){
    return callCC(function($k)use($ms){
        swoole_timer_after($ms,function () use ($k){
            $k(null);
        });
    });
}


function async_dns_lookup($host,$timeout=0){
    if($timeout>0){
        return race([
            callCC(function($k) use($host) {
                swoole_async_dns_lookup($host, function($host, $ip) use($k) {
                    $k($ip);
                });
            }),
            timeout($timeout)
        ]);
    }else{
        return callCC(function($k)use($host){
            swoole_async_dns_lookup($host, function($host, $ip) use($k) {
                $k($ip);
            });
        });
    }
}

function once(callable $fun){
    $has = false;
    return function (...$args)use($fun,&$has){
        if($has === false){
            $fun(...$args);
            $has = true;
        }
    };
}


function timeoutWrapper(callable $fun,$timeout){
    return function ($k)use($fun,$timeout){
        $k = once($k);
        $fun($k);
        swoole_timer_after($timeout,function()use($k){
            $k(null,new \Exception("timeout"));
        });
    };
}

function await($task,...$args){
    if($task instanceof \Generator){
        return $task;
    }
    if(is_callable($task) && ! $task instanceof SysCall){
        $gen = function()use($task,$args){
            yield $task(...$args);
        };
    }else{
        $gen = function () use ($task){
            yield $task;
        };
    }
    return $gen();
}

function race(array $tasks){
    $tasks = array_map(__NAMESPACE__."\\await",$tasks);
    return new SysCall(function(AsyncTask $parent) use ($tasks){
        if(empty($tasks)){
            return null;
        }else{
            return new Any($tasks,$parent);
        }
    });
}

function timeout($ms){
    return callCC(function($k)use($ms){
        swoole_timer_after($ms,function()use($k){
            $k(null,new \Exception("timeout"));
        });
    });
}

function all(array $tasks){
    $tasks = array_map(__NAMESPACE__."\\await",$tasks);
    return new SysCall(function(AsyncTask $parent) use ($tasks){
        if(empty($tasks)){
            return null;
        }else{
            return new All($tasks,$parent);
        }
    });
}

function go(...$args){
    spawn(...$args);
}

/**
 * @param int $n
 * @return Channel
 */
function chan($n=0){
    if($n === 0){
        return new Channel();
    }else{
        return new BufferChannel($n);
    }
}

function fork($task,...$args){
    $task = await($task);
    return new SysCall(function(AsyncTask $parent)use($task){
        return new FutureTask($task,$parent);
    });
}

function array_right_reduce(array $input, callable $function, $initial = null)
{
    return array_reduce(array_reverse($input, true), $function, $initial);
}

function compose(array $middleware)
{
    return function(ServerContext $ctx = null) use($middleware) {
        $ctx = $ctx ?: new ServerContext(); // Context 参见下文
        return array_right_reduce($middleware, function($rightNext, $leftFn) use($ctx) {
            return $leftFn($ctx, $rightNext);
        }, null);
    };
}
