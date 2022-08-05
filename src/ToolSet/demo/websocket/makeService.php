<?php

require "vendor/autoload.php";

use ToolSet\Service\WebSocket\ServiceWebSocket;
use ToolSet\Service\ServiceBase;

$usernameArray = [];

$service = new ServiceWebSocket();
$service->onConnect(function($con) use (&$usernameArray){
    $userName = 'admin';
    $con->otherData = ['username' => $userName];
    $usernameArray[$userName] = $con;
    var_dump("新连接用户,当前链接数:" . count($usernameArray[$userName]));
});
$service->onMessage(function($con, $data) use (&$usernameArray) {
    var_dump($data);
    // 根据链接资源发送信息
    ServiceWebSocket::send($con, [
        'name' => ServiceBase::emptyDefault($data['name']).'-return',
        'msg' => ServiceBase::emptyDefault($data['msg']).'-return'
    ]);
    // 通过指针选择资源发送信息
    ServiceWebSocket::send($usernameArray['admin'], [
        'msg' =>'通过指针选择链接资源进行通讯-return' . ServiceBase::emptyDefault($data['msg']).'-return'
    ]);
});
$service->onClose(function($con) use (&$usernameArray){
    var_dump($con->conId, $con->otherData);
    // 根据指针进行释放
    unset($usernameArray['admin']);
});
ServiceWebSocket::makeService();