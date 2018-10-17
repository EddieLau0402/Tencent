<?php
/**
 * Created by PhpStorm.
 * User: eddie
 * Date: 2018/8/10
 * Time: 下午5:17
 */

namespace JkTech\TencentIm;

class Util
{
    /**
     * POST请求
     *
     * @author Eddie
     *
     * @param $url
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    public static function postRequest($url, $params = [], $headers = [])
    {
        return self::request($url, $params, 'POST', $headers);
    }

    /**
     * GET请求
     *
     * @author Eddie
     *
     * @param $url
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    public static function getRequest($url, $params = [], $headers = [])
    {
        return self::request($url, $params, 'GET', $headers);
    }

    /**
     * 发送请求
     *
     * @author Eddie
     *
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return mixed
     */
    public static function request($url, $params = [], $method = 'GET', $headers = [])
    {
        $client = new \GuzzleHttp\Client();

        $option = [];
        if ($headers) $option['headers'] = $headers;

        if (strtoupper($method) == 'GET') { // >>>>> GET request.
            if ($params) { // has parameters.
                if (is_array($params)) $params = http_build_query($params);
                $url .= (strpos($url, '?') ? '&' : '?') . $params;
                $response = $client->get($url, $option);
            }
        } else {
            $option['body'] = $params;
            $response = $client->post($url, $option);
        }

        return $response->getBody()->getContents();
    }


    /**
     * 下划线转驼峰
     *
     * @autor Eddie
     *
     * @param $str
     * @param bool $ucfirst
     * @return mixed|string
     */
    public static function convertToCamel ($str)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
    }

    /**
     * 生成随机数
     *
     * @author Eddie
     *
     * @return int
     */
    public static function makeMsgRandom ()
    {
        return mt_rand(100000, 999999);
    }

    /**
     * 返回当前时间戳
     *
     * @author Eddie
     *
     * @return int
     */
    public static function getTimestamp()
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'PRC'));
        return time();
    }

}