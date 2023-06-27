<?php

namespace ToolSet\Service\Excel;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use ToolSet\Service\ServiceBase;
use ToolSet\Service\ServiceFile;

class ServiceExcel
{
    public static $fileHasDate = false;
    public static $fileHasTime = false;
    public static $fileSavePath = './excel/export'; // 文件保存的路径
    public static $fileFormat = 'Xls'; // 文件的后缀类型
    public static $lastFileSavePath = null; // 文件存储的路径
    public static $lastFileSavePathIsHasTimePath = false; // 文件存储的路径是否要随机数

    public static $dataAllHorizontallyPosition = null; // 水平位置 left左 center中 right右
    public static $dataAllVerticalPosition = null; // 垂直位置 top局上 center中心 bottom局下

    public static $deleteFileTimeOut = 3600 * 24 * 7; // 删除多久之前生成的文件

    /**
     * 简单导出
     * @param $fieldName // 文件名称
     * @param $titleConfig // 表头配置 例如 ['name' => '用户名', 'phone' => '手机号',...]
     * @param $dataList // 内容列表 例如 [['name' => '张三', 'phone' => '13100000000'],['name' => '李四', 'phone' => '1320000000'],...]
     * @param array $otherParam
     *                      save_path 是否保存在服务器上
     * @return null
     * @throws \Exception
     */
    public static function exportEasy($fileName, $titleConfig, $dataList, $otherParam = [])
    {
        $exportObj = new Export();
        $exportObj->makeSheet('sheet1', $titleConfig, $dataList, $otherParam);

        $fileName = str_replace(' ', '_', $fileName);
        $fileName = str_replace(':', '：', $fileName);

        $setFileName = $fileName;
        if(self::$fileHasDate == true){
            $setFileName = $fileName . ServiceBase::getYmdDate();
        }
        if(self::$fileHasTime == true){
            $setFileName = $fileName . ServiceBase::getYmdHisDate();
        }
        $result = null;
        if(isset($otherParam['is_save_with_last_path']) && $otherParam['is_save_with_last_path'] === true){
            $exportObj->saveFileToPath();
            return $result;
        }
        $exportObj->downloadExcel($setFileName);
        return $result;
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

    /**
     * 导入
     * @param $fileData // 文件数据
     * @param $sheetIndexOrName // sheet页面的下标
     * @param $configData // 字段配置
     * @param null $callbackFunction // 回调方法 function($rowItem, $rowNum)
     * @param array $otherParam 其他参数
     *                    offsetTop 距离顶部偏移量 （跳过多少行之后开始处理）
     *                    isSetFile 是否是指定的文件，有些时候不一定要解析上传的文件，指定的文件也要解析
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function import($fileData, $sheetIndexOrName, $configData, $callbackFunction = null, $otherParam = [])
    {
        return Import::import($fileData, $sheetIndexOrName, $configData, $callbackFunction, $otherParam);
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

    // 设置最新的存储路径
    public static function setLastSavePath($fileName)
    {
        $num = "not_time";
        if(self::$lastFileSavePathIsHasTimePath == true){
            $num = time() . mt_rand(1111, 9999);
        }
        $fileSavePath = self::$fileSavePath;
        $format = self::$fileFormat;
        $savePath = "{$fileSavePath}/{$num}/{$fileName}.{$format}";
        self::$lastFileSavePath = $savePath;
        return $savePath;
    }

    // 获取所有存储的文件
    public static function getAllSaveFile()
    {
        $savePath = self::$fileSavePath;
        if(!is_dir($savePath)){
            return [];
        }
        $files = scandir($savePath);
        $returnFiles = [];
        foreach($files as $file)
        {
            if ($file !== '.' && $file !== '..') {
                $returnFiles[] = "{$savePath}/{$file}";
            }
        }
        return $returnFiles;
    }

    // 删除存储的文件
    public static function deleteSaveFile($filePath)
    {
        if(is_dir($filePath)){
           ServiceFile::forceDeleteDirectory($filePath);
        }
    }

    // 删除过期的文件
    public static function deleteTimeOutFiles()
    {
        echo "<pre>";
        $files = self::getAllSaveFile();
        foreach($files as $filePath)
        {
            $filePathArray = explode('/', $filePath);
            $endPath = end($filePathArray);
            if(!is_numeric($endPath)){
                continue;
            }
            $timeNum = floor(ServiceBase::beDividedBy($endPath, 10000));
            if($timeNum < time() - self::$deleteFileTimeOut){
                self::deleteSaveFile($filePath);
            }
        }
    }
}