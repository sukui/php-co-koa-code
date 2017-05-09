<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午6:32
 */

namespace Sukui\Middleware;

use Sukui\ServerContext;

class ExceptionHandler implements Middleware {
    public function __invoke(ServerContext $ctx, $next) {
        try{
            yield $next;
        }catch (\Exception $ex){

            $status = 500;
            $code = $ex->getCode()?:0;
            $msg = "Internal Error";

            if ($ex instanceof HttpException) {
                $status = $ex->status;
                if ($ex->expose) {
                    $msg = $ex->getMessage();
                }
            }

            $err = [ "code" => $code,  "msg" => $msg ];
            if ($ctx->accept("json")) {
                $ctx->status = 200;
                $ctx->body = $err;
            } else {
                $ctx->status = $status;
                if ($status === 404) {
                    $ctx->body = 404;
                } else if ($status === 500) {
                    $ctx->body = 500;
                } else {
                    $ctx->body = 502;
                }
            }
        }
    }
}