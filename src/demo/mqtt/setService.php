<?php
require "vendor/autoload.php";
use ToolSet\Service\Mqtt\ServiceMqtt;
use ToolSet\Service\Mqtt\ServiceMqttByWorker;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;


$mqttService = new ServiceMqttByWorker('101.132.43.31');
//$mqttService = new ServiceMqtt('101.132.43.31');

$mqttService->subscribe('cs_topic3', function($message){
    echo "订阅1";
    var_dump($message);
});
$mqttService->subscribe('cs_topic4', function($message){
    echo "订阅2";
    var_dump($message);
});

$mqttService->makeService();