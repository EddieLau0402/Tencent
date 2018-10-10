<?php

namespace JkTech\TencentIm\Message;

use JkTech\TencentIm\Message\Entities\Custom;
use JkTech\TencentIm\Message\Entities\Face;
use JkTech\TencentIm\Message\Entities\Location;
use JkTech\TencentIm\Message\Entities\Text;

class Bag
{
    protected $msgType;

    protected $msgContent;

    private $entity;


    private $mapping = [
        'TIMTextElem' => Text::class,
        'TIMCustomElem' => Custom::class,
        'TIMLocationElem' => Location::class,
        'TIMFaceElem' => Face::class,
    ];


    public function __construct(array $data)
    {
        $this->msgType = $data['MsgType'];

        if (isset($data['MsgContent']) && !empty($data['MsgContent'])) {
            if (isset($this->mapping[$this->msgType])) {
                $this->entity = (new \ReflectionClass($this->mapping[$this->msgType]))->newInstanceArgs([$data['MsgContent']]);
            }
        }
    }

    public function format()
    {
        return [
            'MsgType' => $this->msgType,
            'MsgContent' => []
        ];
    }

    private function isType($type)
    {
        return array_flip($this->mapping)[get_class($this->entity)] === ('TIM' . substr($type, 2) . 'Elem');
    }

    public function __get($name)
    {
        if (in_array($name, ['isCustom', 'isText', 'isLocation', 'isFace'])) {
            /// 是否为指定类型消息(如: 自定义消息, 文本消息, 位置消息, 表情消息), 返回值: "true" OR "false"
            return $this->isType($name);
        } else {
            $attr = strtolower($name);
            return $this->entity->$attr;
        }
    }

    public function __call($name, array $args)
    {
        if ( method_exists($this, $name)) {
            call_user_func_array([$this, $name], $args);
        } else {
            // 设置 消息entity 属性值
            $attr = strtolower($name);
            if (isset($this->msgContent[$attr])) {
                $this->msgContent[$attr] = $args[0];
            }
        }
        return $this;
    }

}