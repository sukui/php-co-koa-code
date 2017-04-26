<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 17/3/30
 * Time: 上午10:36
 */
namespace  Sukui\Gen;

class Gen
{
    public $isfirst = true;
    public $generator;

    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function valid()
    {
        return $this->generator->valid();
    }

    public function send($value = null)
    {
        if ($this->isfirst) {
            $this->isfirst = false;
            $data =  $this->generator->current();
        } else {
            $data =  $this->generator->send($value);
        }
        return $data;
    }

    public function throw_(\Exception $e){
        return $this->generator->throw($e);
    }
}
