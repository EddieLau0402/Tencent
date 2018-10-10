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
     * 发送curl请求
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
        /*
         * Open connection, and set options.
         */
        $ch = curl_init();
        if (strtoupper($method) == 'GET') { // >>>>> GET request.
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if ($params) { // has parameters.
                $url .= (strpos($url, '?') ? '&' : '?') . http_build_query($params);
            }
        }
        else { // >>>>> POST request.
            if (is_array($params)) {
                $params = http_build_query($params);
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params)
                ));
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        /*
         * Execute.
         */
        $result = curl_exec($ch);

        /*
         * Close connection.
         */
        curl_close($ch);

        /*
         * Return.
         */
        return $result;
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