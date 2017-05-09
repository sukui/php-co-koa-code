<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午6:30
 */

namespace  Sukui\Middleware;
use Sukui\ServerContext;
interface Middleware{
    public function __invoke(ServerContext $ctx,$next);
}