<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午4:53
 */

namespace Sukui;

class Application{
    public $httpServer;
    public $context;
    public $middleware = [];
    public $fn;
    public $defaultConfig = [
        'port' => 8000,
        'host' => '127.0.0.1'
    ];

    public function __construct() {
        $this->context = new ServerContext();
        $this->context->app = $this;
    }

    public function addMiddleware(callable $fn){
        $this->middleware[] = $fn;
        return $this;
    }

    public function listen($port = 8000,array $config=[]){
        $this->fn = compose($this->middleware);
        $config = ['port'=>$port] + $config + $this->defaultConfig;
        $this->httpServer = new \swoole_http_server($config['host'], $config['port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->httpServer->set($config);
        $this->httpServer->on("request",[$this,"onRequest"]);
        $this->httpServer->start();
    }

    public function onRequest(\swoole_http_request $req, \swoole_http_response $res){
        $ctx = $this->createContext($req,$res);
        $reqHandler = $this->makeRequestHandler($ctx);
        $resHandler = $this->makeResponseHandler($ctx);
        spawn($reqHandler,$resHandler);
    }

    protected function makeRequestHandler(ServerContext $context){
        return function ()use($context){
            yield Context::setCtx("ctx",$context);
            $context->res->status(404);
            $fn = $this->fn;
            yield $fn($context);
        };
    }

    protected function makeResponseHandler(ServerContext $context){
        return function ($r=null,\Exception $ex = null)use ($context){
            if($ex){
                $this->handleError($context);
            }else{
                $this->respond($context);
            }
        };
    }

    protected function handleError(ServerContext $context,\Exception $ex=null){
        if($ex === null){
            return;
        }
        if($ex && $ex->getCode() !== 404){
            echo "something wrong";
            //sys_error($context);
            //sys_error($ex);
        }
        $msg = $ex->getCode();
        if($ex instanceof \HttpException){
            $status = $ex->status?:500;
            $context->res->status($status);
            if($ex->expose){
                $msg = $ex->getMessage();
            }
        }else{
            $context->res->status(500);
        }

        $context->res->header("Content-Type", "text");
        $context->res->write($msg);
        $context->res->end();
    }

    protected function respond(ServerContext $context){
        if($context->respond === false) return;
        $body = $context->body;
        $code = $context->status;
        if($code !== null){
            $context->res->status($code);
        }

        if($body !==  null){
            $context->write($body);
        }
        $context->res->end();
    }

    protected function createContext(\swoole_http_request $req, \swoole_http_response $res){

        $context = clone $this->context;

        $request = $context->request = new Request($this,$context,$req,$res);
        $response = $context->response = new Response($this,$context,$req,$res);
        $context->app = $this;
        $context->req = $req;
        $context->res = $res;

        $context->response = $response;
        $context->request = $request;

        $request->originalUrl = $req->server['request_uri'];
        $request->ip = $req->server['remote_addr'];
        return $context;
    }
}