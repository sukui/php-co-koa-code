<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午6:47
 */

namespace Sukui;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;

class Router extends RouteCollector {

    public $dispatcher = null;
    public function __construct() {
        $routeParser = new RouteParser\Std();
        $dataGenerator = new DataGenerator\GroupCountBased();
        parent::__construct($routeParser, $dataGenerator);
    }

    public function routes(){
        $this->dispatcher =  new GroupCountBased($this->getData());
        return [$this,"dispatch"];
    }

    public function dispatch(ServerContext $context,$next){
        if($this->dispatcher === null){
            $this->routes();
        }

        $uri = $context->url;
        if(false !== $pos = strpos($uri,"?")){
            $uri = substr($uri,0,$pos);
        }
        $uri = rawurldecode($uri);
        $routerInfo = $this->dispatcher->dispatch(strtoupper($context->method),$uri);
        switch ($routerInfo[0]){
            case  Dispatcher::NOT_FOUND:
                $context->status = 404;
                yield $next;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $context->status = 405;
                break;
            case Dispatcher::FOUND:
                $handler = $routerInfo[1];
                $vars = $routerInfo[2];
                yield $handler($context,$next,$vars);
                break;
        }

    }

}

