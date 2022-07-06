<?php

namespace ToolSet\Service\Excel;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use ToolSet\Service\ServiceBase;

class ServiceExcel
{
    public static $fileHasDate = false;
    public static $fileHasTime = false;

    /**
     * 简单导出
     * @param $fieldName // 文件名称
     * @param $titleConfig // 表头配置 例如 ['name' => '用户名', 'phone' => '手机号',...]
     * @param $dataList // 内容列表 例如 [['name' => '张三', 'phone' => '13100000000'],['name' => '李四', 'phone' => '1320000000'],...]
     */
    public static function exportEasy($fieldName, $titleConfig, $dataList)
    {
        $exportObj = new Export();
        $exportObj->makeSheet($fieldName, $titleConfig, $dataList);

        $setFieldName = $fieldName;
        if(self::$fileHasDate == true){
            $setFieldName = $fieldName . ServiceBase::getYmdDate();
        }
        if(self::$fileHasTime == true){
            $setFieldName = $fieldName . ServiceBase::getYmdHisDate();
        }

        $exportObj->downloadExcel($setFieldName);
    }

    /**
     * 导出多个sheet页面
     * @param $fileName // 文件名称
     * @return ExportMany
     */
    public static function exportMany($fileName)
    {
        return new ExportMany($fileName);
    }


    public static function import($fileData, $sheetNumOrName, $configData, $callbackFunction = null)
    {
        return Import::import($fileData, $sheetNumOrName, $configData, $callbackFunction);
    }


    /**
     * 根据key值获取对应的列标名称
     * @param $key
     * @return mixed|string
     */
    public static function getKeyName($key)
    {
        $keyArray = [
            "A", "B", "C", "D", "E",
            "F", "G", "H", "I", "J",
            "K", "L", "M", "N", "O",
            "P", "Q", "R", "S", "T",
            "U", "V", "W", "X", "Y", "Z"
        ];

        // 最多计算到ZZ 再往后的就不计算了
        $keyName = $key;
        if ($key < 26) {
            $keyName = $keyArray[$key];
        } elseif ($key < 702) {
            // 十位数向下取整得出
            $tenFigures = floor($key / 26);
            // 个位数取余数得下标
            $singleDigit = $key % 26;

            $keyName = $keyArray[$tenFigures - 1] . $keyArray[$singleDigit];
        }

        return $keyName;
    }

}