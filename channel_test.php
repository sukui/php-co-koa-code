<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/28
 * Time: 下午2:13
 */

require dirname(__FILE__)."/vendor/autoload.php";

/*
$pingCh = chan();
$pongCh = chan();

go(function ()use($pingCh,$pongCh){
    while (true){
        echo (yield $pingCh->recv());
        yield $pongCh->send("Pong\n");
        yield async_sleep(200);
    }
});

go(function ()use($pingCh,$pongCh){
    while (true){
        echo  (yield $pongCh->recv());
        yield $pingCh->send("Ping\n");
        yield async_sleep(100);
    }
});

go(function ()use($pingCh){
    echo "start up \n";
    yield $pingCh->send("Ping\n");
});*/


/*go(function() {
    $start = microtime(true);

    $ch = chan();

    // 开启一个新的协程，异步执行耗时任务
    spawn(function() use($ch) {
        yield async_sleep(1000);
        yield $ch->send(42); // 通知+传送结果
    });


    $future = (yield fork(function (){
        yield async_sleep(1000);
        yield 43;
    }));

    // 阻塞等待超时，捕获到超时异常
    try {
        $r = (yield $future->get(100));
        var_dump($r);
    } catch (\Exception $ex) {
        echo "get result timeout\n";
    }



    yield async_sleep(500);
    $r = (yield $ch->recv()); // 阻塞等待结果
    echo $r."\n"; // 42

    $r = (yield $future->get());
    echo $r."\n";


    // 我们这里两个耗时任务并发执行，总耗时约1000ms
    echo "cost ", microtime(true) - $start, "\n";
});*/



go(function() {
    $start = microtime(true);

    /** @var $future FutureTask */
    $future = (yield fork(function() {
        yield async_sleep(500);
        yield 42;
    }));

    // 阻塞等待超时，捕获到超时异常
    try {
        $r = (yield $future->get(100));
        var_dump($r);
    } catch (\Exception $ex) {
        echo "get result timeout\n";
    }

    yield async_sleep(1000);

    // 因为我们只等待子任务100ms，我们的总耗时只有 1100ms
    echo "cost ", microtime(true) - $start, "\n";
});

go(function() {
    $start = microtime(true);

    /** @var $future FutureTask */
    $future = (yield fork(function() {
        yield async_sleep(500);
        yield 42;
        throw new \Exception();
    }));

    yield async_sleep(1000);

    // 子任务500ms前发生异常，已经处于完成状态
    // 我们调用get会当即引发异常
    try {
        $r = (yield $future->get());
        var_dump($r);
    } catch (\Exception $ex) {
        echo "something wrong in child task\n";
    }

    // 因为耗时任务并发执行，这里总耗时仅1000ms
    echo "cost ", microtime(true) - $start, "\n";
});