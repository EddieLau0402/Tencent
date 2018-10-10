<?php
/**
 * Created by PhpStorm.
 * User: eddie
 * Date: 2018/8/9
 * Time: 下午5:29
 */

return [
    'appid' => env('IM_APPID'), // SDK appid

    'identifier' => env('IM_IDENTIFIER'), //

    'domain' => 'https://console.tim.qq.com/',

    'version' => 'v4', // 协议版本号, 固定为: v4

    'private_key' => env('IM_PRIVATE_KEY'), // 私钥文件的路径

    'public_key' => env('IM_PUBLIC_KEY'), // 公钥文件的路径

    'sign_expired' => env('IM_SIGN_EXPIRED', 180 * 24 * 3600), // 签名有效时长(单位: 秒); 默认最长180天
];