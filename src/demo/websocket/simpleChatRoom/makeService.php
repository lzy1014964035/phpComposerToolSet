<?php

require "vendor/autoload.php";

use ToolSet\Service\WebSocket\ServiceWebSocket;
use ToolSet\Service\ServiceBase;

class WsService
{
    // 假设一个用户组
    public static $users = [
        [
            'username' => 'zhangsan',
            'password' => '123456',
            'nickname' => '张三',
            'token' => '张三的token',
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
            'token' => '李四的token',
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
            'token' => '王五的token',
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
    public static $userConnectionPool = [];
    public static $tokenConnectionPool = [];

    /**
     * 发送
     * @param $con
     * @param $actionSign
     * @param array $data
     * @param string $msg
     */
    public static function send($con, $actionSign, $data = [], $msg = ""){
        var_dump([
            '推送动作' => $actionSign,
        ]);
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
        self::setRoute('user/login', function($con, $param){ route::login($con, $param); });
        // 断线重连
        self::setRoute('user/DAR', function($con, $param){ route::dar($con, $param); });
        // 用户发送数据
        self::setRoute('message/sendMsg', function($con, $param){ route::userSendMsg($con, $param); });
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
     * 获取聊天室的用户
     * @param $reqRoomId
     * @return mixed
     */
    private static $getRoomsUserCacheTime = 0;
    private static $getRoomsUserCache = [];
    public static function getRoomsUser($reqRoomId)
    {
        // 正常来讲不是一次查出全部，而是查出对应聊天室的用户都有谁
        if(time() - self::$getRoomsUserCacheTime > round(30, 60)){
            $setArray = [];
            foreach(self::$users as $user)
            {
                foreach($user['rooms'] as $value)
                {
                    $roomId = $value['id'];
                    $setArray[$roomId][] = $user;
                }
            }
            self::$getRoomsUserCache = $setArray;
        }
        return self::$getRoomsUserCache[$reqRoomId];
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
            var_dump([
                '执行动作' => $actionSign,
            ]);
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

// 路由
class route{

    // 给用户绑定链接
    private static function bindUserForCon($con, $userData)
    {
        $con->otherData['userData'] = $userData;
        $username = $userData['username'];
        WsService::$userConnectionPool[$username] = $con;
    }
    // 发送用户信息到客户端
    private static function sendUserDataToCli($con)
    {
        $userData = $con->otherData['userData'];

        // 发送请求
        WsService::send($con, 'afterLogin', [
            'nickname' => $userData['nickname'],
            'token' => $userData['token'],
            'rooms' => $userData['rooms'],
        ]);
    }

    // 发起登录
    public static function login($con, $param){
        $username = $param['username'];
        $password = $param['password'];

        // 转成key类型进行操作
        $users = ServiceBase::arrayKeyMakeData(WsService::$users, 'username');

        $userData = isset($users[$username]) ? $users[$username] : null;

        if( ! $userData){
            WsService::sendAlertMsg($con, '找不到该用户名');
            return;
        }

        if(isset(WsService::$userConnectionPool[$username])){
            WsService::sendAlertMsg($con, '该用户已经登录，不可重复登录');
            return;
        }

        if($userData['password'] != $password){
            WsService::sendAlertMsg($con, '密码不正确');
            return;
        }

        self::bindUserForCon($con, $userData);
        self::sendUserDataToCli($con);
    }

    // 用户发送聊天信息
    public static function userSendMsg($con, $param){
        $userData = $con->otherData['userData'];
        $roomId = $param['roomId'];
        $text = $param['text'];

        // 给聊天室里的每个人都发
        $roomUsers = WsService::getRoomsUser($roomId);

        foreach($roomUsers as $user){
            $username = $user['username'];
            if( ! isset(WsService::$userConnectionPool[$username])){
                continue;
            }

            $con = WsService::$userConnectionPool[$username];

            // 发送请求
            WsService::send($con, 'message/listenOtherMsg', [
                'room_id' => $roomId,
                'nickname' => $userData['nickname'],
                'talkMessage' => $text,
                'date' => ServiceBase::getYmdHisDate(),
            ]);
        };
    }

    // 断线重连
    public static function dar($con, $param){
        $token = $param['token'];
        $tokenUsers = ServiceBase::arrayKeyMakeData(WsService::$users, 'token');
        $userData = ServiceBase::emptyDefault($tokenUsers[$token], null);

        if($userData){
            self::bindUserForCon($con, $userData);
            self::sendUserDataToCli($con);
        }else{
            WsService::sendAlertMsg($con, '断线重连失败');
        }
    }
}

WsService::makeService();