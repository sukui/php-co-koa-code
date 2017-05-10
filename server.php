<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午5:47
 */

require dirname(__FILE__)."/vendor/autoload.php";



$router = new \Sukui\Router();

$router->get("/user/{id:\d+}",function (\Sukui\ServerContext $context,$next,$vars){
        $context->body = "user={$vars['id']}";
});


$router->addRoute(["GET","POST"],"/test",function (\Sukui\ServerContext $context,$next,$vars){
    $context->body = "test";
});

$router->get("/timeout",function (\Sukui\ServerContext $context,$next,$vars){
    yield async_sleep(1000);
});


$router->addGroup("/admin",function(\FastRoute\RouteCollector $router){
    $router->addRoute("GET","/test1",function (\Sukui\ServerContext $context,$next,$vars){
        $context->body = "admin/test1";
    });

    $router->addRoute("GET","/test2",function (\Sukui\ServerContext $context,$next,$vars){
        $context->body = "admin/test2 with ".json_encode($vars);
    });
});


$app = new \Sukui\Application();

$app->addMiddleware(new \Sukui\Middleware\RequestTimeout(200));

$app->addMiddleware($router->routes());

$app->addMiddleware(new \Sukui\Middleware\NotFound());

$app->listen();