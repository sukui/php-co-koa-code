<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/30
 * Time: 上午10:35
 */

namespace Sukui\Task;

use Sukui\Gen\Gen;
use Sukui\SysCall;

final class AsyncTask implements Async
{
    public $gen;
    public $continuation;
    public $parent;

    public function __construct(\Generator $gen, Async $task = null)
    {
        $this->gen = new Gen($gen);
        $this->parent = $task;
    }

    public function begin(callable $continuation)
    {
        $this->continuation = $continuation;
        $this->next();
    }

    public function next($result = null,\Exception $ex = null)
    {

        try{
            if($ex){
               $this->gen->throw_($ex);
            }else{
                $value = $this->gen->send($result);
            }
            if ($this->gen->valid()) {

                if($value instanceof SysCall){
                    $value = $value($this);
                }

                if($value instanceof \Generator){
                    $value = new self($value,$this);
                }

                if($value instanceof Async){
                    $async = $value;
                    $continuation = [$this,"next"];
                    $async->begin($continuation);
                }else{
                    $this->next($value,null);
                }
            }else{
                $cc = $this->continuation;
                $cc($result,null);
            }
        }catch (\Exception $ex){
            if($this->gen->valid()){
                $this->next(null,$ex);
            }else{
                $cc = $this->continuation;
                $cc($result,$ex);
            }
        }
    }
}