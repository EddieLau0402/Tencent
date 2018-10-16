<?php

namespace JkTech\TencentIm\Service;

use JkTech\TencentIm\Message\Bag;
use JkTech\TencentIm\Util;

class Message extends AbstractService
{
    const CALLBACK_BEFORE = 'C2C.CallbackBeforeSendMsg';
    const CALLBACK_AFTER  = 'C2C.CallbackAfterSendMsg';

    /*
     * 错误码，0为回调成功；1为回调出错。
     */
    const CALLBACK_SUCCESS_CODE = 0;
    const CALLBACK_FAIL_CODE    = 1;

    /*
     * 请求处理的结果，OK表示处理成功，FAIL表示失败。
     */
    const CALLBACK_SUCCESS_STATUS = 'OK';
    const CALLBACK_FAIL_STATUS    = 'FAIL';

    protected $service = 'openim';

    private $fromAccount;

    private $toAccount;


    private $callbackBefore = false;

    private $callbackAfter = false;

    private $msgBody = [];

    private $msgTime;

    private $offlinePushInfo = [];


    public function __construct()
    {

    }

    /**
     * 发送消息
     *
     * @author Eddie
     *
     * @param $identifier
     * @param array $opt
     * @return array|mixed
     */
    public function send($identifier, array $opt = [])
    {
        $allowOptKeys = [
            'SyncOtherMachine',
            'From_Account',
            'MsgLifeTime'
        ];
        foreach ($opt as $k => $v) {
            if (!in_array($k, $allowOptKeys)) unset($opt[$k]);
            if (empty($v)) unset($opt[$k]);
        }

        // build post-data
        $data = array_merge($opt, [
            'To_Account' => $identifier,
            'MsgRandom'  => Util::makeMsgRandom(),
            'MsgTimeStamp' => Util::getTimestamp(),
            'MsgBody' => $this->msgBody
        ], ['OfflinePushInfo' => $this->offlinePushInfo]);

        try {
            $result = Util::postRequest($this->getUrl('sendmsg'), $data);
            return json_decode($result, true);
        } catch (\Exception $e) {
            return self::makeFailResponse($e->getMessage());
        }

    }

    /**
     * 接收解析
     *
     * @author Eddie
     *
     * @param array $msg
     * @return $this
     */
    public function parse(array $msg)
    {
        foreach ($msg as $attr => $val) {
            switch ($attr) {
                case 'CallbackCommand': // 回调命令
                    if (self::CALLBACK_BEFORE == $val) {
                        $this->callbackBefore = true;
                    } else if (self::CALLBACK_AFTER == $val) {
                        $this->callbackAfter = true;
                    }
                    break;

                case 'MsgBody':
                    foreach ($val as $item) {
                        $this->msgBody[] = new Bag($item);
                    }
                    break;

                default:
                    $attribute = Util::convertToCamel($attr);
                    if (property_exists($this, $attribute)) {
                        $this->$attribute = $val;
                    }
                    break;
            }
        }

        return $this;
    }

    /**
     * 向"MsgBody"添加消息内容
     *
     * @author Eddie
     *
     * @param Bag $msgBag
     * @return $this
     */
    public function append(Bag $msgBag)
    {
        $this->msgBody[] = $msgBag;
        return $this;
    }

    /**
     * 清空"MsgBody"消息内容
     *
     * @author Eddie
     *
     * @return $this
     */
    public function flush()
    {
        $this->msgBody = [];
        return $this;
    }

    /**
     * 离线推送信息配置, 具体可参考"消息格式描述"(https://cloud.tencent.com/document/product/269/2720#.E7.A6.BB.E7.BA.BF.E6.8E.A8.E9.80.81-offlinepushinfo-.E8.AF.B4.E6.98.8E)
     *
     * @author Eddie
     *
     * @param array $info
     * @return $this
     */
    public function setOfflinePush(array $info = [])
    {
        /*
         * TODO : ...
         */
        //$this->offlinePushInfo = $info;

        return $this;
    }


    public function __call($name, $args)
    {
        if (property_exists($this, $name)) {
            $this->$name = $args[0];
        }
        return $this;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            if ($name == 'msgBody') {
                return count($this->msgBody) === 0 ? null : $this->msgBody[0];
            }
            return $this->$name;

        } else {
            switch ($name) {
                case 'is_callback':
                case 'isCallback':
                //case 'callback':
                    return $this->callbackBefore || $this->callbackAfter;

                default:
                    return null;
            }
        }
    }

    public function msgBody($index = null)
    {
        if ($index === null) {
            return $this->msgBody;
        } else {
            if (is_numeric($index)) {
                $len = count($this->msgBody);
                // 超出则返回最后消息
                if (intval($index) >= $len) $index = $len -1;
                return $this->msgBody[$index];
            }
        }
        return null;
    }

    /**
     * 处理发送消息之前回调
     *
     * @author Eddie
     *
     * @param \Closure $callback
     * @return $this
     */
    public function handleCallbackBeforeSend(\Closure $callback)
    {
        if ($this->callbackBefore) {
            $callback($this);
        }
        return $this;
    }

    /**
     * 处理发送消息之后回调
     *
     * @author Eddie
     *
     * @param \Closure $callback
     * @return $this
     */
    public function handleCallbackAfterSend(\Closure $callback)
    {
        if ($this->callbackAfter) {
            $callback($this);
        }
        return $this;
    }


    /**
     * 生成回调成功应答包
     *
     * @author Eddie
     *
     * @return array
     */
    public static function makeSuccessResponse()
    {
        return self::makeCallbackResponse(self::CALLBACK_SUCCESS_STATUS, self::CALLBACK_SUCCESS_CODE);
    }

    /**
     * 生成回调失败应答包
     *
     * @author Eddie
     *
     * @param string $errMsg
     * @return array
     */
    public function makeFailResponse(string $errMsg)
    {
        return self::makeCallbackResponse(self::CALLBACK_FAIL_STATUS, self::CALLBACK_FAIL_CODE, $errMsg);
    }

    /**
     * 生成回调应答包
     *
     * @author Eddie
     *
     * @param string $status
     * @param int $errCode
     * @param string $errMsg
     * @return array
     */
    public static function makeCallbackResponse(string $status, int $errCode, $errMsg ='')
    {
        return [
            'ActionStatus' => in_array($status, [self::CALLBACK_SUCCESS_STATUS, self::CALLBACK_FAIL_STATUS]) ? $status : self::CALLBACK_STATUS_SUCCESS,
            'ErrorCode' => in_array($errCode, [self::CALLBACK_SUCCESS_CODE, self::CALLBACK_FAIL_CODE]) ? $errCode : self::CALLBACK_SUCCESS_CODE,
            'ErrorInfo' => $errMsg
        ];
    }
}