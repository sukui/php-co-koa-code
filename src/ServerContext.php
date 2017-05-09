<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午4:57
 */

namespace Sukui;
class ServerContext{
    public $app;
    public $request;
    public $response;
    public $req;
    public $res;
    public $state = [];
    public $respond =  true;
    public $body;
    public $status;

    public function __call($name, $arguments) {
        $fn = [$this->response,$name];
        if(is_callable($fn)){
            return $fn(...$arguments);
        }else{
            return null;
        }
    }

    public function __get($name) {
        return $this->request->$name;
    }

    public function __set($name, $value) {
        $this->response->$name = $value;
    }

    public function _throw($status,$message){
        if($message instanceof \Exception){
            $ex = $message;
            throw new \HttpException($status,$ex->getMessage(),$ex->getCode(),$ex->getPrevious());
        }else{
            throw new \HttpException($status,$message);
        }
    }

}