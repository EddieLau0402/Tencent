<?php

namespace JkTech\TencentIm\Facade;

use Illuminate\Support\Facades\Facade;

class Im extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'tencent.im';
    }
}