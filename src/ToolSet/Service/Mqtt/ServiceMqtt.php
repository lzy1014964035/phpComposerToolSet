<?php

namespace ToolSet\Service\Mqtt;

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\ConnectionSettings;


// 安装emqx的笔记
// https://blog.csdn.net/weixin_32047681/article/details/115988733
// 操作mqtt的笔记
// https://www.cnblogs.com/emqx/p/15080501.html

class ServiceMqtt
{
    private static $singleCase = null;
    private $mqttObject = null;
    private $mqttSubscribeArray = [];

    public function __construct($server, $port = 1883, $userName = "emqx_user", $password = null, $clientId = null)
    {
        $clientId = $clientId ?: rand(5, 15);
        $clean_session = false;

        $connectionSettings  = new ConnectionSettings();
        $connectionSettings
            ->setUsername($userName)
            ->setPassword($password)
            ->setKeepAliveInterval(60)
            // Last Will 设置
            ->setLastWillTopic('emqx/test/last-will')
            ->setLastWillMessage('client disconnect')
            ->setLastWillQualityOfService(1);

        $mqtt = new MqttClient($server, $port, $clientId);
        $mqtt->connect($connectionSettings, $clean_session);

        $this->mqttObject = $mqtt;
    }

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
     * 推送
     * @param $topic
     * @param $data
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function publish($topic, $data)
    {
        if(is_array($data) || is_object($data)){
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $this->mqttObject->publish($topic, $data, 0, true);
    }

    /**
     * 设置订阅
     * @param $topic
     * @param $callbackFunction
     * @param string $type
     * @throws \Exception
     */
    public function subscribe($topic, $callbackFunction)
    {
        if( ! is_callable($callbackFunction)){
            throw new \Exception('');
        }
        $this->mqttSubscribeArray[$topic] = $callbackFunction;
    }

    /**
     * 挂起服务
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\InvalidMessageException
     * @throws \PhpMqtt\Client\Exceptions\MqttClientException
     * @throws \PhpMqtt\Client\Exceptions\ProtocolViolationException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function makeService()
    {
        foreach ($this->mqttSubscribeArray as $topic => $callback)
        {
            $this->mqttObject->subscribe($topic, function($topic, $message) use ($callback){
                $decodeMsg = json_decode($message, true);
                $message = $decodeMsg ?: $message;
                $callback($message);
            });
        }

        $this->mqttObject->loop(true);
    }



}