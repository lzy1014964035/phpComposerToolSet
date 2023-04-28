<?php

namespace ToolSet\Service\WebSocket;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

// 笔记
// https://www.136.la/nginx/show-185043.html

class ServiceWebSocket
{
    private $workerObj = null;
    private $connectionPool = [];
    private static $connectionId = 0;

    public function __construct($port = '2346', $workCount = 4)
    {
        $this->workerObj = new Worker("websocket://0.0.0.0:{$port}");
        $this->workerObj->count = $workCount;
    }

    /**
     * 连接时
     * @param callable $callbackFunction
     */
    public function onConnect(callable $callbackFunction)
    {
        $this->workerObj->onConnect = function(TcpConnection $connection) use ($callbackFunction){
            $connectionPool[$connection->id] = $connection;
            $callbackFunction($connection);
            $connection->onWebSocketConnect = function($connection , $httpBuffer)
            {
                // 把路由附上
                $connection->path = self::getWsConnectPath($httpBuffer);
            };
        };
    }

    /**
     * 收到信息时
     * @param callable $callbackFunction
     */
    public function onMessage (callable $callbackFunction)
    {
        $this->workerObj->onMessage = function(TcpConnection $connection, $data) use ($callbackFunction){
            $jsonDecodeData = json_decode($data, true);
            if($jsonDecodeData){
                $data = $jsonDecodeData;
            }
            $callbackFunction($connection, $data);
        };
    }

    /**
     * 关闭时
     * @param callable $callbackFunction
     */
    public function onClose(callable $callbackFunction)
    {
        $this->workerObj->onClose = function(TcpConnection $connection) use ($callbackFunction){
            $callbackFunction($connection);
            // 从链接组中删掉
            unset($this->connectionPool[$connection->id]);
        };
    }

    /**
     * 发送信息
     * @param $connection
     * @param array $data
     */
    public static function send($connection, $data = [])
    {
        if(is_array($data) || is_object($data)){
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $connection->send($data);
    }


    public static function getWsConnectPath($wsConect)
    {
        $pattern = '/GET\s+\/\/(.+?)\s+HTTP\/1\.1/';

        if (preg_match($pattern, $wsConect, $matches)) {
            $route = $matches[1] ?? "";
        } else {
            $route = "";
        }
        return $route;
    }

    /**
     * 启动服务
     */
    public static function makeService()
    {
        Worker::runAll();
    }
}