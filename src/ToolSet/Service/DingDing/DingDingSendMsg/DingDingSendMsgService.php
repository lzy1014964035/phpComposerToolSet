<?php


namespace ToolSet\Service\DingDing\DingDingSendMsg;

use ToolSet\Service\ServiceBase;

class DingDingSendMsgService
{

    // 机器人密钥
    private static $webhook = [
        'https://oapi.dingtalk.com/robot/send?access_token=ef0bb6275a212dcb51b49472363757ce2d93e43f137624b5a6da82a20f2f8606',
        'https://oapi.dingtalk.com/robot/send?access_token=1bc617fde60e73c96267f0b6c8c4f34f6ca5a63750d0d89b01e27b77aea5e8c6',
        'https://oapi.dingtalk.com/robot/send?access_token=14046557d374c24da41d55348ae0ee9aeddf9f221149f9242fb3e8e6acbb4be6',
        'https://oapi.dingtalk.com/robot/send?access_token=d052486161acba4d9c6149af26ecf892e88980325993b9f11351b230389da958',
        'https://oapi.dingtalk.com/robot/send?access_token=7f28387f848a448e2d07ffd5603ee33315f3d8aa509023cfbf529c4cd7f99a54',
    ];
    private static $key = "b:";

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
