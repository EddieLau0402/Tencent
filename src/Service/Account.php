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

    private $accountFieldsMap = [
        'Tag_Profile_IM_Nick'            => 'nick',              // 昵称
        'Tag_Profile_IM_Gender'          => 'gender',            // 性别
        'Tag_Profile_IM_BirthDay'        => 'birth',             // 生日
        'Tag_Profile_IM_Location'        => 'location',          // 所在地
        'Tag_Profile_IM_SelfSignature'   => 'self_sign',         // 个性签名
        'Tag_Profile_IM_AllowType'       => 'allow_type',        // 加好友验证方式
        'Tag_Profile_IM_Language'        => 'language',          // 语言
        'Tag_Profile_IM_Image'           => 'faceUrl',           // 头像URL
        'Tag_Profile_IM_MsgSettings'     => 'msg_settings',      // 消息设置
        'Tag_Profile_IM_AdminForbidType' => 'admin_forbid_type', // 管理员禁止加好友标识
        'Tag_Profile_IM_Level'           => 'level',             // 等级
        'Tag_Profile_IM_Role'            => 'role',              // 角色
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
    public function get($identifier, array $option = [])
    {
        $this->service = 'profile';

        $profileFields = array_keys($this->accountFieldsMap);
        // TODO

        $data = [
            'To_Account' => is_array($identifier) ? $identifier : [$identifier],
            'TagList' => $profileFields
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
            //return json_decode($result, true);

            if (isset($result['ActionStatus']) && $result['ActionStatus'] == 'OK') {
                $users = [];
                foreach ($result['UserProfileItem'] ?? [] as $userProfile) {
                    $users[] = $this->parseUserProfile($userProfile);
                }

                return count($users) == 1 ? array_pop($users) : $users;
            }
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }
        return null;
    }

    /**
     * Parse user's profile
     *
     * @author Eddie
     *
     * @param null $data
     * @return array
     */
    protected function parseUserProfile($userProfile = null)
    {
        if (empty($userProfile)) return [];
        if (isset($userProfile['ProfileItem'])) return [];

        $user = ['identifier' => $userProfile['To_Account']];

        foreach ($userProfile['ProfileItem'] as $row) {
            $attr = $this->accountFieldsMap[$row['Tag']] ?? '';

            if (empty($attr)) continue; // no attribute, then next one

            $user[$attr] = $row['Value'] ?? '';
        }

        return $user;
    }

    /**
     * Setter
     *
     * @author Eddie
     *
     * @param $name
     * @param array $args
     * @return $this
     */
    public function __call($name, array $args)
    {
        if (isset($this->attrs[$name])) {
            $this->attrs[$name] = $args[0];
        } else {
            switch ($name) {
                case 'avatar':
                case 'face':
                case 'image':
                    $this->attrs['faceUrl'] = $args[0];
                    break;

                case 'nickname':
                    $this->attrs['nick'] = $args[0];
                    break;
            }
        }
        return $this;
    }

    /**
     * Getter
     *
     * @author Eddie
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        //dd(['name' => $name]);
        if (array_key_exists($name, $this->attrs)) {
            switch ($name) {
                case 'avatar':
                case 'face':
                case 'image':
                    return $this->attrs['faceUrl'];

                case 'nickname':
                    return $this->attrs['nick'];

                default:
                    return $this->attrs[$name];
            }
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