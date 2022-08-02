<?php

namespace ToolSet\Service\WebSocket;

use Workerman\Worker;

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
        $this->workerObj->onConnect = function($connection) use ($callbackFunction){
            $callbackFunction($connection);
            $connection->conId = ++self::$connectionId;
            $connectionPool[$connection->conId] = $connection;
            self::send($connection, ['id' => $connection->conId, 'msg' => "链接成功"]);
        };
    }

    /**
     * 收到信息时
     * @param callable $callbackFunction
     */
    public function onMessage (callable $callbackFunction)
    {
        $this->workerObj->onMessage = function($connection, $data) use ($callbackFunction){
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
        $this->workerObj->onClose = function($connection) use ($callbackFunction){
            $callbackFunction($connection);
            // 从链接组中删掉
            $conId = $connection->conId;
            unset($this->connectionPool[$conId]);
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

    /**
     * 启动服务
     */
    public static function makeService()
    {
        Worker::runAll();
    }
}