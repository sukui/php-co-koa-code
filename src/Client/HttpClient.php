<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/31
 * Time: 下午5:55
 */
namespace Sukui\Client;

class HttpClient extends \swoole_http_client
{
    public function async_get($uri)
    {
        return callCC(function($k) use($uri) {
            $this->get($uri, $k);
        });
    }

    public function async_post($uri, $post)
    {
        return callCC(function($k) use($uri, $post) {
            $this->post($uri, $post, $k);
        });
    }

    public function async_execute($uri)
    {
        return callCC(function($k) use($uri) {
            $this->execute($uri, $k);
        });
    }

    public function awaitGet($uri,$timeout=1000){
        return race([
            callCC(function($k)use($uri){
                $this->get($uri,$k);
            }),
            timeout($timeout)
        ]);
    }
}