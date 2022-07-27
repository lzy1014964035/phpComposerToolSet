<?php

namespace ToolSet\Service;

class ServiceBase
{
    use ServiceAlgorithm;
    use ServiceArray;
    use ServiceMath;
    use ServiceStatisticalChart;
    use ServiceString;
    use ServiceTime;

    // 是否打印,一般是cli脚本才开启该参数
    public static $checkDump = false;

    public static function packTag()
    {
        return "1.0.3";
    }

    public static function requestInput()
    {
        return null;
    }

    /**
     * 抛出异常，用的是默认类
     * 如果后续发现有更标准的类，请麻烦换掉
     * @param $message
     * @throws \ErrorException
     */
    public static function throwException($message)
    {
        throw new \ErrorException($message);
    }


    /**
     * 返回失败信息
     * @param $errorMsg
     * @param array $otherData
     * @param int $errorCode
     * @return false|string
     */
    public static function returnJsonError($errorMsg, $otherData = [], $errorCode = 402)
    {
        $ret = [
            'code' => $errorCode,
            'msg' => $errorMsg,
            'data' => $otherData,
        ];
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        die;
    }

    /**
     * 返回成功信息
     * @param $msg
     * @param array $otherData
     * @return false|string
     */
    public static function returnJsonSuccess($msg, $otherData = [])
    {
        // 要查看的字段
        // 有些业务场景用curl复用接口会比较方便
        // 可是很多时候curl请求拿到的数据量太大
        // 所以增加这个字段，只获取自己要看的内容，剩下的剔除掉。
        $showField = self::requestInput('curl_set_show_field');
        if (!empty($showField) && is_array($showField)) {
            self::makeDataShowField($otherData, $showField);
            // 看下是否有需要替换键名
            $replaceKeys = [];
            foreach ($showField as $showName => $oldName) {
                if (is_string($showName)) {
                    $replaceKeys[$oldName] = $showName;
                }
            }
            if (!empty($replaceKeys)) {
                self::arrayReplaceKey($otherData, $replaceKeys);
            }
        }


        $ret = [
            'code' => 200,
            'msg' => $msg,
            'data' => $otherData,
        ];
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 向上层返回错误信息
     * @param $msg
     * @return array
     */
    public static function returnError($msg)
    {
        return ['errorMsg' => $msg];
    }

    /**
     * 获取下层返回的错误信息
     * @param $errorData
     * @return mixed|null
     */
    public static function getHasError($errorData)
    {
        return isset($errorData['errorMsg']) ? $errorData['errorMsg'] : null;
    }

    /**
     * get请求
     * @param $url
     * @param array $data
     * @param null $header
     * @param bool $returnOriginally 返回原数据
     * @return bool|string
     */
    public static function getCurl($url, $data = [], $header = null, $returnOriginally = false)
    {
        $kvArray = [];
        foreach ($data as $field => $value) {
            $kvArray[] = "$field=$value";
        }

        if ($kvArray) {
            $kvData = implode('&', $kvArray);
            $url = "{$url}?{$kvData}";
        }

        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //跳过SSL
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        $json_data = json_decode($output, true) ?: false;

        return $returnOriginally ? $output : $json_data;
    }

    /**
     * post请求
     * @param $url
     * @param array $data
     * @param null $header
     * @param bool $returnOriginally 返回原数据
     * @return bool|string
     */
    public static function postCurl($url, $data = [], $header = null, $returnOriginally = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //c.http_build_query()支持传递多维数组参数
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);
        $json_data = json_decode($output, true) ?: false;

        return $returnOriginally ? $output : $json_data;
    }


    /**
     * 判断是否是全部字段都为空
     * @param $data
     * @param $fieldArray
     * @return bool
     */
    public static function checkFieldNotAllNull($data, $fieldArray)
    {
        $isNotNull = false;
        foreach ($fieldArray as $field) {
            if (!empty($data[$field])) {
                $isNotNull = true;
                break;
            }
        }
        return $isNotNull;
    }

    /**
     * 获取全局缓存的数据
     * @param $param
     * @param $setValueFunction // 设置值的方法
     * @return mixed
     */
    private static $arrayCacheData = [];

    public static function getSetArrayCache($param, $setValueFunction)
    {
        ksort($param);
        $json_param = json_encode($param, JSON_UNESCAPED_UNICODE);
        if (!isset(self::$arrayCacheData[$json_param])) {
            self::$arrayCacheData[$json_param] = $setValueFunction();
        }
        return self::$arrayCacheData[$json_param];
    }

    /**
     * 检查变量是否存在，不存在就设置个默认值
     * @param $variable
     * @param int $default
     * @return int
     */
    public static function emptyDefault(&$variable, $default = 0)
    {
        if (empty($variable) && $variable !== 0 && $variable !== '0' && $variable !== '') $variable = $default;
        return $variable;
    }

    // emptyDefaultNum  简写
    public static function edn(&$variable, $default = 0)
    {
        return self::emptyDefault($variable, $default);
    }


    /**
     * 打印
     * @param mixed ...$data
     */
    public static function dump(...$data)
    {
        if (self::$checkDump === true) {
            echo "<pre>";
            foreach ($data as $dataValue) {
                var_dump($dataValue);
            }
            echo "</pre>";
        }
    }

    /**
     * 打印
     * @param mixed ...$data
     */
    public static function dd(...$data)
    {
        if (self::$checkDump === true) {
            foreach($data as $v){
                self::dump($v);
            }
            die;
        }
    }


}
