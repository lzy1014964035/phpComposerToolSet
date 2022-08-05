<?php
require "vendor/autoload.php";
use ToolSet\Service\Mqtt\ServiceMqttByWorker;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use ToolSet\Service\ServiceBase;

$mqttService = new ServiceMqttByWorker('101.132.43.31');

$mqttService->publish('cs_topic3', ['msg' => '111', 'time' => time()]);
$mqttService->publish('cs_topic4', '13213123123'.time());

ServiceBase::getLastTimeRecordDifference();

for($i = 1; $i <= 1000; $i++){
    $mqttService->publish('cs_topic3', ['i' => $i, 'md5' => md5($i)]);
}

$useTime = ServiceBase::getLastTimeRecordDifference();

echo $useTime;