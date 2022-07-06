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
     * @param $fieldName
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
     * @param $fieldName
     * @return ExportMany
     */
    public static function exportMany($fieldName)
    {
        return new ExportMany($fieldName);
    }




}