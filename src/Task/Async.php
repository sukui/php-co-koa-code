<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/30
 * Time: 上午11:32
 */

namespace Sukui\Task;

interface Async{
    public function begin(callable $callback);
}