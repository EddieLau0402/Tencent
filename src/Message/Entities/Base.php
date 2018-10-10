<?php

namespace JkTech\TencentIm\Message\Entities;

abstract class Base
{
    protected $fillable = [];

    /**
     * Attributes of message-entity
     *
     * @var array
     */
    private $attrs = [];


    public function __construct()
    {
        $args = func_get_args();
        foreach ($args[0] ?? [] as $k => $v) {
            $k = strtolower($k);
            if (in_array($k, $this->fillable)) {
                $this->attrs[$k] = $v;
            }
        }
    }

    public function __get($name)
    {
        return isset($this->attrs[$name]) ? $this->attrs[$name] : null;
    }

    public function __set($name, $value)
    {
        if (isset($this->attrs[$name])) $this->attrs[$name] = $value;
    }
}