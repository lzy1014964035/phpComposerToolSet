<?php

namespace lzy1014964035\PHPComposerToolSet\Service;

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

    public static function requestInput(){
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
     * 获取数组中对应的一些字段的键和值，适用于一维数组
     * @param $array
     * @param $fieldArray
     * @return array
     */
    public static function getArrayFieldsKeyAndValue($array, $fieldArray)
    {
        $returnArray = [];
        foreach ($fieldArray as $field) {
            if (isset($array[$field])) {
                $returnArray[$field] = $array[$field];
            }
        }
        return $returnArray;
    }

    /**
     * 给数组替换key
     * @param $array
     * @param $keyConfig
     */
    public static function arrayReplaceKey(&$array, $keyConfig)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                self::arrayReplaceKey($value, $keyConfig);
            }
            if (isset($keyConfig[$key]) && $keyConfig[$key] != $key) {
                $array[$keyConfig[$key]] = $value;
                unset($array[$key]);
                continue;
            }
        }
    }

    /**
     * 获取对应的头部内容
     * @param $string
     * @param $headerStrArray
     * @return string
     */
    public static function getHeaderString($string, $headerStrArray)
    {
        $returnString = "";
        $len = strlen($string);
        $num = 0;
        while ($num < $len) {
            $str = $string[$num];
            if (in_array($str, $headerStrArray)) {
                $returnString .= $str;
            } else {
                break;
            }
            $num++;
        }
        return $returnString;
    }

    /**
     * 获取一个时间段内的每周的时间组
     * @param string $startTime
     * @param string $endTime
     * @param bool $hasWeekend
     * @return array
     */
    public static function getWeekDateArray(string $startTime, string $endTime, $hasWeekend = true)
    {
        $dayS = 86400;
        $time = strtotime($startTime);
        $weekArray = [[]];
        $weekDaysArray = [7, 1, 2, 3, 4, 5, 6];
        while ($time <= strtotime($endTime)) {
            $weekDay = date('w', $time);
            $weekDay = $weekDaysArray[$weekDay];
            if (($hasWeekend == true) || ($hasWeekend == false && $weekDay < 6)) {
                $weekArray[count($weekArray) - 1][] = BaseService::getYmdDate($time);
            }

            if ($weekDay >= 7) {
                $weekArray[] = [];
            }
            $time += $dayS;
        }
        foreach ($weekArray as $key => $value) {
            if (empty($value)) {
                unset($weekArray[$key]);
            }
        }
        return $weekArray;
    }

    /**
     * 操作数组（用于计算合计之类的）
     * @param $array
     * @param $callBackFunction
     * @return array
     * @throws \ErrorException
     */
    public static function activeArrayData($array, $callBackFunction)
    {
        if (!is_callable($callBackFunction)) {
            BaseService::throwException('数组操作请传入有效的回调');
        }
        $setArray = $array[0];
        $returnArray = [];
        self::activeArrayDataWithRecursion($setArray, $array, $returnArray, $callBackFunction);
        return $returnArray;
    }

    // 操作数组 - 递归处理
    private static function activeArrayDataWithRecursion($data, $array, &$returnArray, $callBackFunction)
    {
        foreach ($data as $key => $value) {
            $arrayColumn = array_column($array, $key);
            if (is_array($value)) {
                $returnArray[$key] = [];
                self::activeArrayDataWithRecursion($value, $arrayColumn, $returnArray[$key], $callBackFunction);
            } else {
                $returnArray[$key] = $callBackFunction($key, $arrayColumn, $returnArray);
            }
        }
    }

    /**
     * 获取一个月份所在季度拥有的月份
     * @param $month
     * @return array|bool
     */
    public static function getMonthQuarterMonths($month)
    {
        if (!strtotime($month)) {
            return false;
        }
        $year = explode('-', $month)[0];
        $quarter = self::getMonthQuarter($month);
        $months = self::getQuarterMonths($year, $quarter);
        return $months;
    }

    /**
     * 获取月份所属的季度
     * @param $month
     * @return mixed
     */
    public static function getMonthQuarter($month)
    {
        $month = strtotime($month);
        $month = date("m", $month);
        $month = (int)$month;
        $quarterConfig = [
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 2,
            7 => 3,
            8 => 3,
            9 => 3,
            10 => 4,
            11 => 4,
            12 => 4,
        ];
        return $quarterConfig[$month];
    }

    /**
     * 获取一个季度拥有的月份
     * @param $year
     * @param $quarter
     * @return array
     */
    public static function getQuarterMonths($year, $quarter)
    {
        $quarterConfig = [
            1 => [
                '01',
                '02',
                '03'
            ],
            2 => [
                '04',
                '05',
                '06'
            ],
            3 => [
                '07',
                '08',
                '09'
            ],
            4 => [
                '10',
                '11',
                '12'
            ],
        ];
        $quarterConfigData = $quarterConfig[$quarter];
        $monthArray = [];
        foreach ($quarterConfigData as $monthName) {
            $monthArray[] = "{$year}-{$monthName}";
        }
        return $monthArray;
    }

    /**
     * 数组排序
     * @param $array
     * @param $keys
     * @param string $sort
     * @return array
     */
    public static function arraySort($array, $keys, $sort = 'asc')
    {
        $newArr = $valArr = array();
        foreach ($array as $key => $value) {
            $valArr[$key] = $value[$keys];
        }
        ($sort == 'asc') ? asort($valArr) : arsort($valArr);
        reset($valArr);
        foreach ($valArr as $key => $value) {
            $newArr[$key] = $array[$key];
        }
        return $newArr;
    }

    /**
     * 获取所属季度中至此的月份
     * @param $month
     * @return array
     */
    public static function getQuarterWithNowMonth($month)
    {
        $quarter = BaseService::getMonthQuarter($month);
        $year = BaseService::getDateYearString($month);
        $monthArray = BaseService::getQuarterMonths($year, $quarter);
        foreach ($monthArray as $key => $monthString) {
            if (strtotime($monthString) > strtotime($month)) {
                unset($monthArray[$key]);
            }
        }
        return $monthArray;
    }

    /**
     * 获取两个Month之间的所有月份的数组
     * @param null $monthOne
     * @param null $monthTwo
     * @return array
     */
    public static function getTwoMonthBetweenMonthArray($monthOne = null, $monthTwo = null)
    {
        if (is_array($monthOne)) {
            $monthTwo = $monthOne[count($monthOne) - 1];
            $monthOne = $monthOne[0];
        }
        $monthOne = strtotime($monthOne);
        $monthOne = date("Y-m", $monthOne);
        $monthTwo = strtotime($monthTwo);
        $monthTwo = date("Y-m", $monthTwo);

        $returnArray = [];

        while (true) {
            if (strtotime($monthOne) <= strtotime($monthTwo)) {
                $returnArray[] = $monthOne;
                $monthOne = strtotime("{$monthOne} +1 month");
                $monthOne = date("Y-m", $monthOne);
            } else {
                break;
            }
        }

        return $returnArray;
    }


    /**
     * 打印
     * @param mixed ...$data
     */
    public static function dump(...$data)
    {
        if (self::$checkDump === true) {
            foreach ($data as $dataValue) {
                dump($dataValue);
            }
        }
    }

    /**
     * 打印
     * @param mixed ...$data
     */
    public static function dd(...$data)
    {
        if (self::$checkDump === true) {
            dd($data);
        }
    }

    /**
     * 获取内容中的所有为月份的key
     * @param $data
     * @return array
     */
    public static function getDataMonthKeyArray($data)
    {
        $monthArray = [];
        foreach ($data as $key => $value) {
            if (strtotime($key) > 0) {
                $monthArray[] = $key;
            }
        }
        return $monthArray;
    }

    /**
     * 搜索前后缀之间的字符串
     * @param $string
     * @param $prefix
     * @param $suffix
     * @return string
     */
    public static function searchStringWithPS($string, $prefix, $suffix)
    {
        $start = strpos($string, $prefix);
        if ($start === false) {
            return false;
        }
        $start = $start + strlen($prefix);
        $end = strpos($string, $suffix, $start);
        if ($end === false) {
            return false;
        }
        $searchString = substr($string, $start, $end - $start);
        return $searchString;
    }

    /**
     * 获取最大的月份
     * @param $lastMonth
     * @return false|string
     */
    public static function getMaxMonth($lastMonth)
    {
        // 最大只能取到上月月底的数据
        $maxMonth = strtotime(date("Y-m") . " -1 month");
        if (strtotime($lastMonth) > $maxMonth) {
            $lastMonth = date("Y-m", $maxMonth);
        }
        return $lastMonth;
    }

}
