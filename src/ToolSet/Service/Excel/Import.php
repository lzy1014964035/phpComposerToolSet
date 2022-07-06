<?php

namespace ToolSet\Service\Excel;

use \PhpOffice\PhpSpreadsheet\IOFactory;

class Import
{
    public static function import($fileData, $sheetNumOrName, $configData, $callbackFunction = null)
    {
        $fileSize = $fileData['size'];
        $fileExtendName = substr(strrchr($fileData["name"], '.'), 1);

        if (is_uploaded_file($fileData['tmp_name'])) {
            // 有Xls和Xlsx格式两种
            if(strtolower($fileExtendName) == "xlsx"){
                $objReader = IOFactory::createReader('Xlsx');
            }else{
                $objReader = IOFactory::createReader('Xls');
            }

            $filename = $fileData['tmp_name'];
            $objPHPExcel = $objReader->load($filename);  //$filename可以是上传的表格，或者是指定的表格
            if(is_numeric($sheetNumOrName)){
                $sheet = $objPHPExcel->getSheet($sheetNumOrName);   //excel中的第一张sheet
            }else{
                $sheet = $objPHPExcel->getSheetByName($sheetNumOrName);
            }

            // 转化成列表
            $list = $sheet->toArray();

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

            $fieldConfig = [];
            foreach($list[0] as $keyNum => $titleName)
            {
                if($titleName === null){
                    break;
                }

                if(isset($configData[$titleName])){
                    $field = $configData[$titleName];
                    $fieldConfig[$field] = $keyNum;
                }
            }

            $returnList = [];
            foreach($list as $key => $value){
                if($key == 0){
                    continue;
                }
                $setData = [];
                foreach($fieldConfig as $field => $fieldKey)
                {
                    $setData[$field] = $value[$fieldKey];
                }
                if(is_callable($callbackFunction)){
                    $callbackFunction($setData, $key + 1);
                }
                $returnList[] = $setData;
            }

            return $returnList;
        }
    }
}