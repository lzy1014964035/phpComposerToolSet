<?php


namespace ToolSet\Service\DingDing\DingDingSendMsg;

use ToolSet\Service\ServiceBase;

class ServiceDingDingSendMsg
{

    // 机器人密钥
    private static $webhook = [

    ];
    private static $key = "b:";

    /**
     * 设置机器人组
     * @param array $setArray
     */
    public static function setWebHook(array $setArray)
    {
        self::$webhook = $setArray;
    }

    /**
     * 设置
     * @param string $key
     */
    public static function setKey(string $key)
    {
        self::$key = $key;
    }

    /**
     * 做个简易的均衡
     * 如果超出 那么这个资源将被禁用十分钟。
     * @return mixed
     */
    private static function getWebHook()
    {
        $time = time();
        $key = $time % count(self::$webhook);
        return self::$webhook[$key];
    }


    private static function request_by_curl($remote_server, $post_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 不用开启curl证书验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        //$info = curl_getinfo($ch);
        //var_dump($info);
        curl_close($ch);
        return $data;
    }

    /**
     * 发送数据
     * @param $content
     * @return bool
     */
    private static function toSendText($content)
    {
        try{
            if(is_array($content)){
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
            }
            $webhook = self::getWebHook();
            if( ! $webhook){
                return false;
            }

            $key = self::$key;

            // text类型
            $textString = json_encode([
                'msgtype' => 'text',
                'text' => [
                    "content" => "{$key}{$content}"
                ],
                'at' => [
                    'isAtAll' => false
                ]
            ], JSON_UNESCAPED_UNICODE);
            $result = self::request_by_curl($webhook, $textString);
            return $result;
        }catch (\Exception $e){
            return false;
        }
    }


    /**
     * 发送数据到钉钉
     * @param $data
     */
    public static function dingdingSendData($data)
    {
        self::toSendText([
            'time' => ServiceBase::getYmdHisDate(),
            'data' => $data,
        ]);
    }

    /**
     * 发送错误到钉钉
     * @param $errorData
     */
    public static function dingdingSendError($errorData)
    {
        self::toSendText([
            'time' => ServiceBase::getYmdHisDate(),
            'errorData' => $errorData,
        ]);
    }


}
