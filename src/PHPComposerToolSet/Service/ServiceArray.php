<?php

namespace ToolSet\Service;


trait ServiceArray
{

    /**
     * 数组键值反转
     * @param $array // 值重复时会覆盖
     * @return array
     */
    public static function arrayKVReversal($array)
    {
        $returnArray = [];
        foreach ($array as $key => $value) {
            $returnArray[$value] = $key;
        }
        return $returnArray;
    }

    /**
     * 获取数组中某个字段值的合集
     * @param $array
     * @param $field
     * @return array
     */
    public static function arrayFieldValues($array, $field)
    {
        $returnArray = [];
        foreach ($array as $value) {
            if (isset($value[$field])) {
                $returnArray[] = $value[$field];
            }
        }
        return $returnArray;
    }

    /**
     * 从新排列数组 让数组中的某个 key的值 作为下标来从新排列数组
     * @param $data
     * @param $keyFields // 字段名称 或者 字段合集
     * @param string $valueField
     * @param bool $returnArray // 是否返回数组
     * @param bool $returnKeysArray // 是否以指针方式返回
     * @return array|bool]
     */
    public static function arrayKeyMakeData($data, $keyFields, $valueField = "", $returnArray = false, $returnKeysArray = false)
    {
        if (!is_array($data)) {
            return false;
        }
        $newData = [];
        foreach ($data as $value) {
            // 通过指定内容生成新的KEY
            if (is_string($keyFields)) {
                $newKey = $value[$keyFields];
            } else {
                // 如果指定的内容是个数组，那么就进行合并处理
                $keyFieldArray = [];
                foreach ($keyFields as $field) {
                    $keyFieldArray[] = $value[$field];
                }
                $newKey = self::getImplodeKey($keyFieldArray);
            }

            // 创建引用
            if ($returnKeysArray === false) {
                self::emptyDefault($newData[$newKey], []);
                // 如果是key模式，就用key做引用
                $setData = &$newData[$newKey];
            } else {
                // 如果是keyArray模式，就用keyArray做引用
                $newKeyArray = self::disassembleImplodeKey($newKey);
                $setData = &$newData;
                foreach ($newKeyArray as $fieldData) {
                    self::emptyDefault($setData[$fieldData], []);
                    $setData = &$setData[$fieldData];
                }
            }

            // 处理引用
            if ($returnArray === true) {
                $setData[] = !empty($valueField) ? $value[$valueField] : $value;
            } else {
                $setData = !empty($valueField) ? $value[$valueField] : $value;
            }
        }
        return $newData;
    }

    /**
     * 获取一个特定格式的K
     * @param $dataArray
     * @return string
     */
    public static function getImplodeKey($dataArray)
    {
        return implode('|', $dataArray);
    }

    /**
     * 拆解KEY
     * @param $key
     * @return array
     */
    public static function disassembleImplodeKey($key)
    {
        if (strpos($key, '|') !== false) {
            return explode('|', $key);
        } else {
            return [$key];
        }
    }

    /**
     * 合并多个数组，根据key进行处理
     * @param mixed ...$arrayItem
     * @return array
     */
    public static function arrayMergeByKey(...$arrayItem)
    {
        $returnArray = [];
        foreach ($arrayItem as $array) {
            foreach ($array as $field => $value) {
                $returnArray[$field] = $value;
            }
        }
        return $returnArray;
    }

    /**
     * 清除数组中的所有小数点数据的小数点后为
     * @param $array
     * @param array $continueArray
     */
    public static function clearDecimalPoint(&$array, $continueArray = [])
    {
        foreach ($array as $key => &$value) {
            if ($key && in_array($key, $continueArray)) {
                continue;
            }
            if (is_array($value) && !empty($value)) {
                self::clearDecimalPoint($value, $continueArray);
            }
            if (is_string($value) && strpos($value, '.') > -1 && strpos($value, '%') < -1) {
                $value = ServiceString::formatToNum($value);
                $value = round($value, 2);
                $value = ServiceString::numToFormat($value, 2);

                // 去除小数点
//                $value = explode('.', $value)[0];
            }
        }
    }


    /**
     * 递归处理字段
     * @param $array
     * @param array $fieldArray
     * @param null $callbackFunction
     */
    public static function recursiveProcessingField(&$array, $fieldArray = [], $callbackFunction = null)
    {
        if (empty($fieldArray) || empty($array) || empty($callbackFunction)) {
            return;
        }
        // 只是不让下面显红色
        if (empty($callbackFunction)) {
            $callbackFunction = function () {
            };
        }

        foreach ($array as $field => &$value) {
            if (is_array($value) && !empty($value)) {
                self::recursiveProcessingField($value, $fieldArray, $callbackFunction);
            }
            if (!is_array($value) && !is_object($value) && in_array($field, $fieldArray)) {
                $setValue = $callbackFunction($value, $field, $array);
                if ($setValue !== null) {
                    $value = $setValue;
                }
            }
        }
    }

    /**
     * 设置数组,使其只保留显示的字段
     * @param $data // 要处理的数组
     * @param $showField // 只保留，只要看的字段
     */
    public static function makeDataShowField(&$data, $showField)
    {
        foreach ($data as $key => &$value) {
            // 对键进行比对
            if (!is_int($key) && !in_array($key, $showField)) {
                // in_array 有BUG 对 int 0 不识别
                unset($data[$key]);
                continue;
            }
            // 对多层数组进行处理
            if (is_array($value)) {
                self::makeDataShowField($value, $showField);
            }
        }
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
     * 操作数组（用于计算合计之类的）
     * @param $array
     * @param $callBackFunction
     * @return array
     * @throws \ErrorException
     */
    public static function activeArrayData($array, $callBackFunction)
    {
        if (!is_callable($callBackFunction)) {
            ServiceBase::throwException('数组操作请传入有效的回调');
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

}