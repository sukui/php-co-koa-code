<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/30
 * Time: 下午2:07
 */
namespace Sukui\Task;

class AsyncDns implements Async {
    public function begin(callable $cc){
        swoole_async_dns_lookup("www.baidu.com",function ($host,$ip)use($cc){
            $cc($ip);
        });
    }
}