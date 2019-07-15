<?php

namespace Eddie\TencentIm\Message\Entities;

class Location extends Base
{
    protected $fillable = ['desc', 'latitude', 'longitude'];
}