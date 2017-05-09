<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/5/9
 * Time: 下午6:44
 */

namespace Sukui\Middleware;

use Sukui\ServerContext;

class NotFound implements Middleware {
    public function __invoke(ServerContext $ctx, $next) {
        yield $next;

        if($ctx->status !== 404 || $ctx->body){
            return;
        }
        $ctx->status = 404;

        if ($ctx->accept("json")) {
            $ctx->body = [
                "message" => "Not Found",
            ];
            return;
        }

        $ctx->body = "<h1>404 Not Found</h1>";

    }
}