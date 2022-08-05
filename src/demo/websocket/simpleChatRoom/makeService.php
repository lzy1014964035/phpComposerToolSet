<?php

require "vendor/autoload.php";

use ToolSet\Service\WebSocket\ServiceWebSocket;
use ToolSet\Service\ServiceBase;

class xxxxxService
{
    // 假设一个用户组
    private static $users = [
        [
            'username' => 'zhangsan',
            'password' => '123456',
            'nickname' => '张三',
            'token'  => '张三的token',
            'rooms' => [ // 拥有的聊天室权限
                [
                    'id' => 'room1',
                    'name' => '聊天室1',
                ],
                [
                    'id' => 'room2',
                    'name' => '聊天室2',
                ],
            ],
        ],
        [
            'username' => 'lisi',
            'password' => '123456',
            'nickname' => '李四',
            'token'  => '李四的token',
            'rooms' => [
                [
                    'id' => 'room1',
                    'name' => '聊天室1',
                ],
            ],
        ],
        [
            'username' => 'wangwu',
            'password' => '123456',
            'nickname' => '王五',
            'token'  => '王五的token',
            'rooms' => [
                [
                    'id' => 'room2',
                    'name' => '聊天室2',
                ],
            ],
        ],
    ];
    private static $actionRoutes = [];

    private static $WSServiceObj = null;
    private static $userConnectionPool = [];

    /**
     * 发送
     * @param $con
     * @param $actionSign
     * @param array $data
     * @param string $msg
     */
    public static function send($con, $actionSign, $data = [], $msg = ""){
        ServiceWebSocket::send($con, [
            'msg' => $msg,
            'data' => $data,
            'actionSign' => $actionSign,
        ]);
    }

    /**
     * 发送提示数据
     * @param $con
     * @param $msg
     */
    public static function sendAlertMsg($con, $msg)
    {
        self::send($con, 'alterMsg', [], $msg);
    }

    /**
     *  设置所有的路由
     */
    public static function setAllRoute()
    {
        // 登录
        self::setRoute('user/login', function($con, $param){ self::login($con, $param); });
        // 用户发送数据
        self::setRoute('message/sendMsg', function($con, $param){ self::userSendMsg($con, $param); });
    }

    /**
     * 设置路由
     * @param $actionName
     * @param $callbackFunction
     */
    public static function setRoute($actionName, $callbackFunction)
    {
        self::$actionRoutes[$actionName] = $callbackFunction;
    }

    /**
     * 发起登录
     * @param $con
     * @param $param
     */
    public static function login($con, $param){
        $username = $param['username'];
        $password = $param['password'];

        // 转成key类型进行操作
        $users = ServiceBase::arrayKeyMakeData(self::$users, 'username');

        $userData = isset($users[$username]) ? $users[$username] : null;

        if( ! $userData){
            self::sendAlertMsg($con, '找不到该用户名');
            return;
        }

        if(isset(self::$userConnectionPool[$username])){
            self::sendAlertMsg($con, '该用户已经登录，不可重复登录');
            return;
        }

        if($userData['password'] != $password){
            self::sendAlertMsg($con, '密码不正确');
            return;
        }

        $con->otherData['userData'] = $userData;

        self::$userConnectionPool[$username] = $userData;

        // 发送请求
        self::send($con, 'afterLogin', [
            'nickname' => $userData['nickname'],
            'token' => $userData['token'],
            'rooms' => $userData['rooms'],
        ]);
    }

    private static $getRoomsUserCacheTime = 0;
    private static $getRoomsUserCache = [];

    /**
     * 获取聊天室的用户
     * @param $roomId
     * @return mixed
     */
    public static function getRoomsUser($roomId)
    {
        // 正常来讲不是一次查出全部，而是查出对应聊天室的用户都有谁
        if(time() - self::$getRoomsUserCacheTime > round(30, 60)){
            $setArray = [];
            foreach(self::$users as $user)
            {
                foreach($user['rooms'] as $value)
                {
                    $roomId = $value['roomId'];
                    $setArray[$roomId][] = $user;
                }
            }
            self::$getRoomsUserCache = $setArray;
        }
        return self::$getRoomsUserCache[$roomId];
    }

    // 用户发送聊天信息
    public static function userSendMsg($con, $param){
        $userData = $con->otherData['userData'];
        $roomId = $param['roomId'];
        $text = $param['text'];

        // 给聊天室里的每个人都发
        $roomUsers = self::getRoomsUser($roomId);

        foreach($roomUsers as $user){
            $username = $user['username'];
            if( ! isset(self::$userConnectionPool[$username])){
                continue;
            }

            $con = self::$userConnectionPool[$username];

            // 发送请求
            self::send($con, 'message/listenOtherMsg', [
                'room_id' => $roomId,
                'nickname' => $userData['nickname'],
                'talkMessage' => $text,
                'date' => ServiceBase::getYmdDate(),
            ]);
        };
    }


    public static function makeService(){
        $service = new ServiceWebSocket();
        self::$WSServiceObj = $service;
        $service->onConnect(function($con) use (&$usernameArray){
            var_dump("新连接用户");
        });
        // 设置路由
        self::setAllRoute();
        // 设置挂载回调
        $service->onMessage(function($con, $data) use (&$usernameArray) {
            $actionSign = $data['actionSign'];
            $param = $data['param'];
//            var_dump($actionSign, $param, $data);
            if( ! self::$actionRoutes[$actionSign]){
                self::sendAlertMsg($con, "执行失败，动作“{$actionSign}”未定义");
            }
            if( ! is_callable(self::$actionRoutes[$actionSign])){
                self::sendAlertMsg($con, "执行失败，动作“{$actionSign}”为无效定义的动作");
            }
            self::$actionRoutes[$actionSign]($con, $param);
        });
        // 断开连接时
        $service->onClose(function($con){
            // 从连接池中剔除
            $username = $con->otherData['userData']['username'];
            unset(self::$userConnectionPool[$username]);
        });
        ServiceWebSocket::makeService();
    }

}

xxxxxService::makeService();