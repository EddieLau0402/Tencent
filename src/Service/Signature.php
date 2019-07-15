<?php

namespace Eddie\TencentIm\Service;

if (version_compare(PHP_VERSION, '5.6.0') < 0 &&
    version_compare(PHP_VERSION, '5.5.10') < 0 &&
    version_compare(PHP_VERSION, '5.4.29') < 0) {
    trigger_error('need php 5.4.29|5.5.10|5.6.0 or newer', E_USER_ERROR);
}
if (!extension_loaded('openssl')) {
    trigger_error('need openssl extension', E_USER_ERROR);
}
if (!in_array('sha256', openssl_get_md_methods(), true)) {
    trigger_error('need openssl support sha256', E_USER_ERROR);
}
//if (version_compare(PHP_VERSION, '7.1.0') >= 0 && !in_array('secp256k1', openssl_get_curve_names(), true)) {
//    trigger_error('not support secp256k1', E_USER_NOTICE);
//}

class Signature extends AbstractService
{
    private $privateKey;

    private $publicKey;

    private $appid;

    public function __construct()
    {
        $this->appid = config('im.appid');
        $this->publicKey = config('im.public_key');
        $this->privateKey = config('im.private_key');
    }

    /**
     * 签名生成
     *
     * @param $identifier
     * @param int $expired
     */
    public function generate($identifier, $expired = 180 * 24 * 3600)
    {
        $json = [
            'TLS.account_type' => '0',
            'TLS.identifier' => (string) $identifier,
            'TLS.appid_at_3rd' => '0',
            'TLS.sdk_appid' => (string) config('im.appid'),
            'TLS.expire_after' => (string) $expired,
            'TLS.version' => '201512300000',
            'TLS.time' => (string) time()
        ];
        $err = '';
        $content = $this->genSignContent($json, $err);
        $signature = $this->genSign($content, $err);
        $json['TLS.sig'] = base64_encode($signature);
        if ($json['TLS.sig'] === false) {
            throw new \Exception('base64_encode error');
        }
        $json_text = json_encode($json);
        if ($json_text === false) {
            throw new \Exception('json_encode error');
        }
        $compressed = gzcompress($json_text);
        if ($compressed === false) {
            throw new \Exception('gzcompress error');
        }
        return $this->base64Encode($compressed);

    }

    /**
     * 签名校验
     *
     * @param $sign
     * @param $identifier
     */
    public function verify($sign, $identifier)
    {
        try {
            $error_msg = '';
            $decoded_sig = $this->base64Decode($sign);
            $uncompressed_sig = gzuncompress($decoded_sig);
            if ($uncompressed_sig === false) {
                throw new \Exception('gzuncompress error');
            }
            $json = json_decode($uncompressed_sig);
            if ($json == false) {
                throw new \Exception('json_decode error');
            }
            $json = (array) $json;
            if ($json['TLS.identifier'] !== $identifier) {
                throw new \Exception("identifier error sigid:{$json['TLS.identifier']} id:{$identifier}");
            }
            if ($json['TLS.sdk_appid'] != $this->appid) {
                throw new \Exception("appid error sigappid:{$json['TLS.appid']} thisappid:{$this->appid}");
            }
            $content = $this->genSignContent($json);
            $signature = base64_decode($json['TLS.sig']);
            if ($signature == false) {
                throw new \Exception('sig json_decode error');
            }
            $succ = $this->verifySign($content, $signature);
            if (!$succ) {
                throw new \Exception('verify failed');
            }
            //$initTime = $json['TLS.time'];
            //$expireTime = $json['TLS.expire_after'];
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }



    /**
     * 根据json内容生成需要签名的buf串
     *
     * @param array $json 票据json对象
     * @return string 按标准格式生成的用于签名的字符串
     * 失败时返回false
     */
    private function genSignContent(array $json)
    {
        $content = '';
        static $aid3rd = 'TLS.appid_at_3rd';
        if (isset($json[$aid3rd])) {
            $content .= "{$aid3rd}:{$json[$aid3rd]}\n";
        }
        static $members = [
            'TLS.account_type',
            'TLS.identifier',
            'TLS.sdk_appid',
            'TLS.time',
            'TLS.expire_after'
        ];
        foreach ($members as $member) {
            if (!isset($json[$member])) {
                throw new \Exception('json need ' . $member);
            }
            $content .= "{$member}:{$json[$member]}\n";
        }
        return $content;
    }


    /**
     * ECDSA-SHA256签名
     *
     * @param string $data 需要签名的数据
     * @return string 返回签名 失败时返回false
     */
    private function genSign($data)
    {
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKey));
        if ($privateKey === false) {
            throw new \Exception(openssl_error_string());
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $privateKey, 'sha256')) {
            throw new \Exception(openssl_error_string());
        }
        return $signature;
    }

    /**
     * 验证ECDSA-SHA256签名
     *
     * @param string $data 需要验证的数据原文
     * @param string $sign 需要验证的签名
     * @return int 1验证成功 0验证失败
     */
    private function verifySign($data, $sign)
    {
        $publicKey = openssl_pkey_get_public(file_get_contents($this->publicKey));
        if ($publicKey === false) {
            throw new \Exception(openssl_error_string());
        }

        $ret = openssl_verify($data, $sign, $publicKey, 'sha256');
        if ($ret == -1) {
            throw new \Exception(openssl_error_string());
        }
        return $ret;
    }

    /**
     * 用于url的base64encode
     * '+' => '*', '/' => '-', '=' => '_'
     *
     * @param string $string 需要编码的数据
     * @return string 编码后的base64串，失败返回false
     */
    private function base64Encode($string)
    {
        static $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $base64 = base64_encode($string);
        if ($base64 === false) {
            throw new \Exception('base64_encode error');
        }
        return str_replace(array_keys($replace), array_values($replace), $base64);
    }

    /**
     * 用于url的base64decode
     * '+' => '*', '/' => '-', '=' => '_'
     *
     * @param string $base64 需要解码的base64串
     * @return string 解码后的数据，失败返回false
     */
    private function base64Decode($base64)
    {
        static $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $string = str_replace(array_values($replace), array_keys($replace), $base64);
        $result = base64_decode($string);
        if ($result == false) {
            throw new \Exception('base64_decode error');
        }
        return $result;
    }
}