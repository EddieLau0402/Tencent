## 腾讯IM ##

### 环境需求

- PHP >= 5.6
- openssl 扩展



### 安装

+ 命令行执行

```shell
$ composer require jk-tech/tencent-im
```

若安装出现
```shell
[InvalidArgumentException]
Could not find a version of package eddie/tencent matching your minimum-stability (dev). Require it with an explicit version constraint allowing its desired stability.
```

可以在composer.json文件 `require` 中添加 

```json
{
  "require": {
    "jk-tech/tencent-im": "dev-master"
  }
}
```
并添加 `repositories` 镜像源
```json
"repositories": {
    "tencent-im": {
        "type": "vcs",
        "url": "https://tech%40jkweixin.net:tech%40jkweixin.net@github.com/techjkweixin/TencentIm.git"
    }
},
```

运行 ```composer update```



+ 添加注册
    - Laravel
        * Laravel 5.5使用包自动发现，所以不需要手动添加ServiceProvider; 版本小于Laravel 5.5需在 `config/app.php` 中注册服务提供者:

        ```php
        'providers' => [
            ...
            
            JkTech\TencentIm\Provider\ImServiceProvider::class,
            
            ...
        ]
        
        'aliases' => [
            ...
            
            'TencentIm' => JkTech\TencentIm\Provider\ImServiceProvider::class,
            
            ...
        ]
        ```
    
    - Lumen
        * 在 `bootstrap/app.php` 中添加
        ```php
        $app->register(\JkTech\TencentIm\Provider\ImServiceProvider::class);
        ```



### 配置

+ 用发布命令将包配置复制到本地配置

```shell
$ php artisan vendor:publish --provider="JkTech\TencentIm\Im\ImServiceProvider"
```

+ 配置内容可以在`.env`中配置

```shell
IM_APPID=12345678
IM_IDENTIFIER=account_identifier
IM_SIGN_EXPIRED=15552000
IM_PRIVATE_KEY=/your_private_key_path/private_key
IM_PUBLIC_KEY=/your_public_key_path/public_key
```


### 使用

1. 签名 `signature` 
    ```php
    /*
     * 生成签名
     */
    $identifier = 'user';
    $sign = TencentIm::signature()->generate($identifier);
    
    /*
     * 签名校验
     */
    if ( TencentIm::signature()->verify($sign, $identifier) ) {
        echo "success";
    } else {
        echo "fail";
    }
    ```

2. 消息 `message`
    ```php
    /*
     * 解析IM回调消息
     */
    $message = TencentIm::message()->parse(request()->all());
    
    /*
     * 读取消息
     */
    $message->fromAccount; // 消息发送方帐号
    $message->toAccount; // 消息接收方帐号
    $message->isCallback; // 或 "$message->is_callback", 是否IM回调, 返回"true"、"false"
    $message->callbackBefore; // 是否发送消息之前回调, 返回"true"、"false"
    $message->callbackAfter; // 是否发送消息之后回调, 返回"true"、"false"
    $message->msgTime; // 消息时间戳，unix 时间戳。
     
    // 一条消息可包括多种消息元素, 所以"msgBody"为数组
    $message->msgBody; // 消息内容, 默认返回"msgBody"数组的第一个元素
    $message->msgBody(); // 返回"msgBody"数组
    $message->msgBody($index); // 根据传入的索引值返回"msgBody"对应元素, 若传入的索引值大于"msgBody"数组长度, 则返回最后元素
 
    $message->msgBody->text; // 文本消息 - 消息内容
    $message->msgBody->data; // 自定义消息 - 自定义消息数据
    $message->msgBody->desc; // 自定义消息 - 自定义消息描述信息
    $message->msgBody->ext; // 自定义消息 - 扩展字段
    /*
     * 注:
     *     详细参考腾讯IM [消息格式描述](https://cloud.tencent.com/doc/product/269/%E6%B6%88%E6%81%AF%E6%A0%BC%E5%BC%8F%E6%8F%8F%E8%BF%B0)
     *     属性名 以 IM消息格式中所定义的字段名的小驼峰命名
     */
    
    $message->handleCallbackBeforeSend(function ($message) { // 处理发送消息之前回调
        // ...
    })
    $message->handleCallbackAfterSend(function ($message) { // 处理发送消息之后回调
        // ...
    })
 
    /*
     * 发送消息
     */
    // 单发单聊消息
    TencentIm::message()
    ->append(new \JkTech\TencentIm\Message\Bag([
         'MsgType' => 'TIMTextElem',
         'MsgContent' => [
             'Text' => 'hello, world'
         ]
     ]))
    ->send('to_account', ['From_Account' => 'from_account']);
    // 批量发单聊消息
    TencentIm::message()
    ->append(new \JkTech\TencentIm\Message\Bag([
         'MsgType' => 'TIMTextElem',
         'MsgContent' => [
             'Text' => 'hello, world'
         ]
     ]))
     ->batchSend(['to_account1', 'to_account2'], ['From_Account' => 'from_account']);
    ```
    
3. 账号 `account`
    ```php
    $account = TencentIm::account(); // 获取服务
 
    $account->identifier('demo')             // 用户名, 必填
        ->faceUrl('http://xxxx/avatar.png')  // 用户头像URL
        ->nick('Jack')                       // 用户昵称
        ->setRobot()                         // 设置帐号类型; 值: 0->表示普通帐号, 1->表示机器人帐号
        ->save()                             // 保存用户信息, 提交到IM
    ;
 
    /*
     * 拉取资料
     */
    $identifier = 'user_123'; // Or ['user_111', 'user_222']; // 获取多个用户信息传入数组类型; (注:每次拉取的用户数不得超过100个)
    $info = $account->get($identifier);
    ```