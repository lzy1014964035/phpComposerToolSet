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

    public function __construct($param = [])
    {
        $port = $param['port'] ?? '2346';
        $workCount = $param['workCount'] ?? 4;
        $ssl = $param['ssl'] ?? null;

        $socketName = "websocket://0.0.0.0:{$port}";
        $context = [];
        if($ssl){
            $context['ssl'] = [
                'local_cert'  => $ssl['cert_path'], // 你的证书文件路径
                'local_pk'    => $ssl['key_path'], // 你的私钥文件路径
                'verify_peer' => false,
            ];
            $socketName = "websocket+ssl://0.0.0.0:{$port}";
        }

        $this->workerObj = new Worker($socketName, $context);
        $this->workerObj->count = $workCount;

        if($ssl){
            $this->workerObj->transport = 'ssl';
        }
    }

    /**
     * 连接时
     * @param callable $callbackFunction
     */
    public function onConnect(callable $onConnect = null, callable $afterConnect = null)
    {
        $this->workerObj->onConnect = function($connection) use ($onConnect, $afterConnect){
            if($onConnect)$onConnect($connection);
            $connection->conId = ++self::$connectionId;
            $connectionPool[$connection->conId] = $connection;
            $connection->onWebSocketConnect = function ($connection, $httpHeader) use ($afterConnect) {
                $path = self::getHeaderStringPath($httpHeader);
                $connection->urlPath = $path;
                if($afterConnect)$afterConnect($connection);
            };
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
    public function onClose(callable $callbackFunction = null)
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

    /**
     * 获取路径
     * @param $requestHeaderString
     * @return mixed|string
     */
    private static function getHeaderStringPath($requestHeaderString)
    {
        $pattern = '/GET\s+\/\/(.+?)\s+HTTP\/1\.1/';

        $route = "";
        if (preg_match($pattern, $requestHeaderString, $matches)) {
            $route = $matches[1];
        }
        return $route;
    }
}