<?php

namespace JkTech\TencentIm\Service;

use JkTech\TencentIm\Util;

class Account extends AbstractService
{
    //protected $service = 'im_open_login_svc';

    private $attrs = [
        'identifier' => '', // 用户名，长度不超过 32 字节
        'nick'       => '', // 用户昵称
        'faceUrl'    => '', // 用户头像URL
        'type'       => 0   // 帐号类型，开发者默认无需填写，值0表示普通帐号，1表示机器人帐号
    ];

    private $accountFields = [
        'Tag_Profile_IM_Nick',            // 昵称
        'Tag_Profile_IM_Gender',          // 性别
        'Tag_Profile_IM_BirthDay',        // 生日
        'Tag_Profile_IM_Location',        // 所在地
        'Tag_Profile_IM_SelfSignature',   // 个性签名
        'Tag_Profile_IM_AllowType',       // 加好友验证方式
        'Tag_Profile_IM_Language',        // 语言
        'Tag_Profile_IM_Image',           // 头像URL
        'Tag_Profile_IM_MsgSettings',     // 消息设置
        'Tag_Profile_IM_AdminForbidType', // 管理员禁止加好友标识
        'Tag_Profile_IM_Level',           // 等级
        'Tag_Profile_IM_Role',            // 角色
    ];

    function __construct()
    {
        //
    }

    /**
     * 独立模式账号导入
     *
     * @param $identifier
     */
    public function save()
    {
        $this->service = 'im_open_login_svc';

        $data = [
            'Identifier' => $this->attrs['identifier'],
            'Nick'       => $this->attrs['nick'],
            'FaceUrl'    => $this->attrs['faceUrl'],
            'Type'       => $this->attrs['type']
        ];
        $data = array_filter($data);

        $url = $this->getUrl('account_import') . '?' . http_build_query([
                'usersig' => (new Signature())->generate(config('im.identifier')), // 主账号签名
                'identifier' => config('im.identifier'), // 主账号
                'sdkappid' => config('im.appid'),
                'random' => Util::makeMsgRandom(),
                'contenttype' => 'json'
            ]);
        //dd(['url' => $url, 'data' => $data]);

        try {
            $result = Util::postRequest($url, json_encode($data));
            return json_decode($result, true);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 独立模式帐号批量导入接口
     *
     * @param array $identifiers
     */
    public function import(array $identifiers)
    {
        $data = [];
        /*
         * TODO ...
         */
        echo '独立模式帐号批量导入';

        $res = $this->postRequest($this->getUrl('multiaccount_import'), $data);
    }

    /**
     * 拉取资料
     *
     * @author Eddie
     *
     * @param $identifier
     * @return mixed
     * @throws \Exception
     */
    public function get($identifier)
    {
        // https://console.tim.qq.com/v4/profile/portrait_get?usersig=xxx&identifier=admin&sdkappid=88888888&random=99999999&contenttype=json
        $this->service = 'profile';

        $data = [
            'To_Account' => is_array($identifier) ? $identifier : [$identifier],
            'TagList' => $this->accountFields
        ];

        $url = $this->getUrl('portrait_get') . '?' . http_build_query([
                'usersig' => (new Signature())->generate(config('im.identifier')), // 主账号签名
                'identifier' => config('im.identifier'),
                'sdkappid' => config('im.appid'),
                'random' => Util::makeMsgRandom(),
                'contenttype' => 'json'
            ]);

        //dd(['url' => $url, 'data' => $data]);

        try {
            $result = Util::postRequest($url, json_encode($data));
            return json_decode($result, true);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function __call($name, array $args)
    {
        if (array_key_exists($name, $this->attrs)) {
            $this->attrs[$name] = $args[0];
        } else if ($name === 'nickname') {
            $this->attrs['nick'] = $args[0];
        }
        return $this;
    }

    public function __get($name)
    {
        //dd(['name' => $name]);
        if (array_key_exists($name, $this->attrs)) {
            return $this->attrs[$name];
        }
        return null;
    }

    public function setRobot()
    {
        $this->attrs['type'] = 1;
        return $this;
    }

    public function isRobot()
    {
        return $this->attrs['type'] === 0;
    }

}