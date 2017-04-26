<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/30
 * Time: 下午2:07
 */
namespace Sukui\Task;

class AsyncSleep implements Async {
    public function begin(callable $cc){
        swoole_timer_after(1000,$cc);
    }
}