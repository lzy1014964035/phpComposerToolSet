<?php

namespace ToolSet\Service\Excel;

use \PhpOffice\PhpSpreadsheet\IOFactory;
use \Exception;
use ToolSet\Service\ServiceBase;

class Import
{
    /**
     * 导入
     * @param $fileData // 文件数据
     * @param $sheetIndexOrName // sheet页面的下标
     * @param $configData // 字段配置
     * @param null $callbackFunction // 回调方法 function($rowItem, $rowNum)
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function import($fileData, $sheetIndexOrName, $configData, $callbackFunction = null, $otherParam = [])
    {
        $offsetTop = isset($otherParam['offsetTop']) ? $otherParam['offsetTop'] : 0;
        $isSetFile = isset($otherParam['isSetFile']) ? $otherParam['isSetFile'] : false;
        $fileSize = $fileData['size'];
        $fileExtendName = substr(strrchr($fileData["name"], '.'), 1);

        if (is_uploaded_file($fileData['tmp_name']) || $isSetFile) {
            // 有Xls和Xlsx格式两种
            if(strtolower($fileExtendName) == "xlsx"){
                $objReader = IOFactory::createReader('Xlsx');
            }else{
                $objReader = IOFactory::createReader('Xls');
            }

            $filename = $fileData['tmp_name'];
            $objPHPExcel = $objReader->load($filename);  //$filename可以是上传的表格，或者是指定的表格
            if(is_numeric($sheetIndexOrName)){
                $sheet = $objPHPExcel->getSheet($sheetIndexOrName);   //excel中的第一张sheet
            }else{
                $sheet = $objPHPExcel->getSheetByName($sheetIndexOrName);
                if($sheet === null){
                    throw new Exception("excel导入失败 sheet页{{$sheetIndexOrName}}不存在");
                }
            }

            // 转化成列表
            $list = $sheet->toArray();

            // 如果存在偏移量，就先剔除偏移范围的行
            if($offsetTop > 0){
                for($i = 0; $i < $offsetTop; $i++){
                    unset($list[$i]);
                }
                $list = array_values($list);
            }

            // 剔除全空的行
            foreach($list as $key => $value)
            {
                $allNotHas = true;
                foreach($value as $vv){
                    if($vv !== null){
                        $allNotHas = false;
                        break;
                    }
                }
                if($allNotHas){
                    unset($list[$key]);
                }
            }
            $list = array_values($list);


            $titleArray = $list[0];
            foreach($titleArray as $key => $value)
            {
                if($value === null){
                    unset($titleArray[$key]);
                }
            }

            $titleArray = array_flip($titleArray);
            $fieldConfig = [];
            foreach($configData as $fieldName => $field)
            {
                if( ! isset($titleArray[$fieldName])){
                    continue;
//                    throw new Exception("excel导入失败 字段{{$fieldName}}再列中不存在");
                }
                $fieldConfig[$field] = $titleArray[$fieldName];
            }

            $returnList = [];
            foreach($list as $key => $value){
                if($key == 0){
                    continue;
                }
                $setData = [];
                foreach($fieldConfig as $field => $fieldKey)
                {
                    $fieldValue = $value[$fieldKey];
                    if(is_string($fieldValue) && strpos($fieldValue, "\n") !== false){
                        $fieldValue = explode("\n", $fieldValue);
                        $value[$fieldKey] = $fieldValue;
                    }
                    $setData[$field] = $value[$fieldKey];
                }
                if(is_callable($callbackFunction)){
                    $dealData = $callbackFunction($setData, $key + 1);
                    if($dealData)$setData = $dealData;
                }
                $returnList[] = $setData;
            }

            return $returnList;
        }
    }
}