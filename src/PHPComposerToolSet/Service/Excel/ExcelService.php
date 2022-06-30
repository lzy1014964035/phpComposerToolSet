<?php

namespace ToolSet\Service\Excel;

use App\Service\BaseService;
use Maatwebsite\Excel\Facades\Excel;

class ExcelService
{
    /**
     * 制作导入模板
     * @param $fileName
     * @param $fieldConfig
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function exportTemplate($fileName, $fieldConfig)
    {
        $excel = Export::makeTemplate($fieldConfig);
        return Excel::download($excel, $fileName . '.xlsx');
    }

    /**
     * 一次性导出数据（适用于导出数据量不大的情况）
     * @param $fileName // 文件名称
     * @param $header // 标题
     * @param $data // 导出的内容 包含标题
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function oneTimeExport($fileName, $header, $data)
    {
        //执行导出
        $excel = new Export($data, $header);
        $excel->setColumnAutoWidth();
//        $excel->setColumnWidth(['A' => 40, 'B' => 40]);
//        $excel->setRowHeight([1 => 40, 2 => 50]);
//        $excel->setFont(['A1:Z1265' => '宋体']);
//        $excel->setFontSize(['A1:I1' => 14, 'A2:Z1265' => 10]);
//        $excel->setBold(['A1:Z2' => true]);
//        $excel->setBackground(['A1:A1' => '808080', 'C1:C1' => '708080']);
//        $excel->setMergeCells(['A1:I1']);
//        $excel->setBorders(['A2:D5' => '#000000']);
        return Excel::download($excel, $fileName . '.xlsx');
    }

    /**
     * 导入excel
     * @param $fileData
     * @param $callbackFunction
     * @param array $headerAlias
     * @return array
     */
    public static function loadExcel($fileData, $callbackFunction, $headerAlias = [])
    {
        // 设置超时时常为300秒
        ini_set("max_execution_time", "360");  // 开启6分钟爬取时间设置
        ini_set("memory_limit", "3000M");  // 开启爬取时间设置

        // 获取数据
        $data = Excel::toArray(new TasksImport(), $fileData);
        $data = $data[0];
        $header = $data[0];
        unset($data[0]);

        // 过滤掉全为null的内容
        foreach ($data as $key => $value) {
            $isAllNull = true;
            foreach ($value as $fieldValue) {
                if ($fieldValue !== null) {
                    $isAllNull = false;
                }
            }
            if ($isAllNull) {
                unset($data[$key]);
            }
        }
        $data = array_values($data);

        // 如果具有别名，那么就进行别名覆盖
        if (!empty($headerAlias)) {
            $setHeader = [];
            foreach ($headerAlias as $headerName => $aliasName) {
                foreach ($header as $key => $value) {
                    if ($value == $headerName) {
                        $value = $aliasName;
                        $setHeader[$key] = $value;
                    }
                }
            }
            $header = $setHeader;
        }

        // 执行回调进行特殊处理
        foreach ($data as $key => &$value) {
            $setValue = [];
            foreach ($header as $keyNum => $aliasName) {
                $setValue[$aliasName] = self::loadValueInit($value[$keyNum]);
            }
            $value = $setValue;
            if (is_callable($callbackFunction)) {
                $callbackFunction($value, $key + 2);
            }
        }

        return $data;
    }

    /**
     * 加载内容处理
     * @param $value
     * @return mixed
     */
    private static function loadValueInit($value)
    {
        // 清除换行符
        $value = str_replace("\n", "", $value);
        return $value;
    }



    /**
     * 转化导入excel的时间
     * @param $excelTime
     * @param string $date
     * @return false|string
     */
    public static function excelTime($excelTime)
    {
        $excelTime = str_replace("\/", "-", $excelTime);

        if(strpos($excelTime, '-') < -1){
            $t1 = intval(($excelTime - 25569) * 3600 * 24); //转换成1970年以来的秒数
            $excelTime = gmdate("Y-m-d", $t1);//格式化时间
        }

        return $excelTime;
    }

}
