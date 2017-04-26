<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/31
 * Time: 下午4:02
 */
namespace Sukui;

use Sukui\Task\Async;

class SysCall{

    private $fun;
    public function __construct(callable $fun){
        $this->fun = $fun;
    }

    public function __invoke(Async $task) {
        $cc = $this->fun;
        return $cc($task);
    }
}