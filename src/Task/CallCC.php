<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/31
 * Time: 下午5:49
 */

namespace Sukui\Task;

class CallCC implements Async {
    public $fun;
    public function __construct(callable $fun) {
        $this->fun = $fun;
    }

    public function begin(callable $callback) {
        $fun = $this->fun;
        $fun($callback);
    }
}