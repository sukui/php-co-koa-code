<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午5:47
 */

require dirname(__FILE__)."/vendor/autoload.php";

$app = new \Sukui\Application();

$app->addMiddleware(function(\Sukui\ServerContext $ctx) {
    $ctx->status = 200;
    $ctx->body = "<h1>Hello World</h1>";
});

$app->listen();