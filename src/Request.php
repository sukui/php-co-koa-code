<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午5:13
 */

namespace Sukui;

class Request{
    public $app;
    public $req;
    public $res;
    public $ctx;
    public $response;
    public $originalUrl;
    public $ip;
    public function __construct(Application $app, ServerContext $ctx, \swoole_http_request $req, \swoole_http_response $res) {
        $this->app = $app;
        $this->ctx = $ctx;
        $this->req = $req;
        $this->res = $res;
    }

    public function __get($name) {
        switch ($name){
            case 'rawcontent':
                return $this->req->rawContent();
            case 'post':
                return isset($this->req->post)?$this->req->post:[];
            case 'get':
                return isset($this->req->get)?$this->req->get:[];
            case 'cookie':
            case 'cookies':
                return isset($this->req->cookie) ? $this->req->cookie : [];
            case "request":
                return isset($this->req->request) ? $this->req->request : [];
            case "header":
            case "headers":
                return isset($this->req->header) ? $this->req->header : [];
            case "files":
                return isset($this->req->files) ? $this->req->files : [];
            case "method":
                return $this->req->server["request_method"];
            case "url":
            case "origin":
                return $this->req->server["request_uri"];
            case "path":
                return isset($this->req->server["path_info"]) ? $this->req->server["path_info"] : "";
            case "query":
            case "querystring":
                return isset($this->req->server["query_string"]) ? $this->req->server["query_string"] : "";
            case "host":
            case "hostname":
                return isset($this->req->header["host"]) ? $this->req->header["host"] : "";
            case "protocol":
                return $this->req->server["server_protocol"];
            default:
                return $this->req->$name;
        }
    }
}