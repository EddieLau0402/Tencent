<?php
namespace JkTech\TencentIm\Service;

use JkTech\TencentIm\Util;

abstract class AbstractService
{
    protected $service;

    /*
     * 签名 (请求IM接口必填参数)
     */
    protected $sig;

    public function getUrl($cmd)
    {
        $url = config('im.domain') . config('im.version') . '/' . $this->service . '/' . $cmd;
        return $url;
    }

    /**
     * 设置签名
     *
     * @author Eddie
     *
     * @param $sig
     * @return $this
     */
    public function sig($sig)
    {
        $this->sig = $sig;
        return $this;
    }
}