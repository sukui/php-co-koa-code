<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/31
 * Time: 下午4:14
 */

namespace Sukui;
use Sukui\Task\Async;

class Context{
    public static function getCtx($key,$default=null){
        return new SysCall(function(Async $task) use ($key,$default){
            while ($task->parent && $task = $task->parent);
            if(isset($task->gen->generator->$key)){
                return $task->gen->generator->$key;
            }else{
                return $default;
            }
        });
    }

    public static function setCtx($key,$val){
        return new Syscall(function(Async $task) use($key, $val) {
            while($task->parent && $task = $task->parent);
            $task->gen->generator->$key = $val;
        });
    }
}