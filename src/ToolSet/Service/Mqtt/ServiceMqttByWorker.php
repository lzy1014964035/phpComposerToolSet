<?php

namespace ToolSet\Service\Mqtt;

use Workerman\Worker;
use Workerman\Mqtt\Client;

// 笔记
// https://blog.csdn.net/Simplegif/article/details/124568405

class ServiceMqttByWorker
{
    private static $singleCase = null;
    private $mqttSubscribeArray = [];
    private $onWorkerStart = null;
    private $publishObj = null;

    /**
     * 获取单例
     * @param $server
     * @param int $port
     * @param string $userName
     * @param null $password
     * @param null $clientId
     * @return ServiceMqtt|null
     */
    public static function getSingleCase($server, $port = 1883, $userName = "emqx_user", $password = null, $clientId = null)
    {
        if( ! self::$singleCase){
            self::$singleCase = new self($server, $port, $userName, $password, $clientId);
        }
        return self::$singleCase;
    }

    /**
     * 构建函数
     * ServiceMqttByWorker constructor.
     * @param $server
     * @param int $port
     * @param string $userName
     * @param null $password
     * @param null $clientId
     */
    public function __construct($server, $port = 1883, $userName = "emqx_user", $password = null, $clientId = null)
    {
        // workerman的推送不方便，所以用另一个组件进行推送
        $this->publishObj = new ServiceMqtt($server, $port, $userName, $password, $clientId);
        // 保存初始化回调
        $this->onWorkerStart = function () use ($port, $server, $userName, $password, $clientId){
//            if( ! $clientId){
//                $clientId = rand(0, 100000) . time();
//            }
//            $options = [
//                'keepalive' => 60,
//                'clean_session' => true,
//                'client_id' => $clientId,
//                'debug' => true,
//                'username' => $userName,
//                'password' => $password,
//                'ssl' => [
//                    'local_pk' => './mqtt_ssl/privkey.pem',
//                    'verify_peer' => false,
//                ],
//            ];
            
            $clicke = "mqtt://{$server}:{$port}";

//            $mqtt = new Client($clicke, $options);
            $mqtt = new Client($clicke);

            // 链接时对注册的主题组进行订阅
            $mqtt->onConnect = function($mqtt) {
                $topicArray = array_keys($this->mqttSubscribeArray);
                foreach($topicArray as $topic){
                    $mqtt->subscribe($topic);
                }
            };

            // 接收信息时，根据主题执行不同的回调
            $mqtt->onMessage = function($topic, $message){
                if( ! isset($this->mqttSubscribeArray[$topic])){
                    return;
                }
                $jsonDecodeData = json_decode($message, true);
                if($jsonDecodeData){
                    $message = $jsonDecodeData;
                }
                $this->mqttSubscribeArray[$topic]($message, $topic);
            };
            $mqtt->connect();
        };
    }

    /**
     * 推送
     * ps：workerman的publish操作不好用，用另一个组件的
     * @param $topic
     * @param $data
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function publish($topic, $data)
    {
        $this->publishObj->publish($topic, $data);
    }

    /**
     * 设置订阅
     * @param $topic
     * @param $callbackFunction
     */
    public function subscribe($topic, $callbackFunction)
    {
        $this->mqttSubscribeArray[$topic] = $callbackFunction;
    }

    /**
     * 挂起服务
     */
    public function makeService()
    {
        $worker = new Worker();
        $worker->onWorkerStart = $this->onWorkerStart;
        Worker::runAll();
    }

}