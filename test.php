<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/28
 * Time: 下午2:13
 */
use Sukui\Client\HttpClient;
use Sukui\Task\AsyncDns;
use Sukui\Task\AsyncSleep;
use Sukui\Context;

require dirname(__FILE__)."/vendor/autoload.php";



//$test = new stdClass();
//$test->parent = new stdClass();
//while ($test->parent && $k = $test->parent);
//var_dump($k);
//exit();



function newSubGen(){
    //yield 0;
    throw new Exception("test");
    yield 1;
}


function newGen()
{

    try{
        $r1 = (yield newSubGen());
    }catch (Exception $e){
        var_dump($e->getMessage());
    }

    $r2 = (yield 2);
    //var_dump($r1,$r2);
    $start = time();
    yield new AsyncSleep();
    echo time()-$start."\n";
    $ip = (yield new AsyncDns());
    yield "ip:{$ip}";
}

function setTask(){
    yield Context::setCtx("test","gggg");
}

function ctxTest(){
    yield setTask();
    $test = (yield Context::getCtx("test"));
    var_dump($test);
}

//$task = new AsyncTask(ctxTest());

$test = function ($result,$ex){
    if($ex){
        var_dump($ex->getMessage());
    }else{
        var_dump($result);
    }
};

//$task->begin($test); // output: 12

/*spawn(function() {
    try {
        yield async_dns_lookup("www.xxx.com", 1);
    } catch (\Exception $ex) {
        echo $ex->getMessage(); // ex!
    }
});*/



// 这里!
spawn(function() {
    try{

        $ip = (yield async_dns_lookup("www.baidu.com"));
        $cli = new HttpClient($ip, 80);
        $cli->setHeaders(["foo" => "bar"]);
        $cli = (yield $cli->async_get("/"));
        echo $cli->body, "\n";

        $ip = (yield race([
            async_dns_lookup("www.baidu.com"),
            timeout(100)
        ]));
        $res = (yield (new HttpClient($ip,80))->awaitGet("/"));
        var_dump($res->statusCode);

        $r = (yield all([
            "bing" => async_dns_lookup("www.bing.com"),
            "so" => async_dns_lookup("www.so.com"),
            "baidu" => async_dns_lookup("www.baidu.com"),
        ]));

        var_dump($r);

    }catch (Exception $e){
        var_dump($e->getMessage());
    }
    swoole_event_exit();
});


