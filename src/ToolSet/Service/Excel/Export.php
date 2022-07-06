<?php

namespace ToolSet\Service\Excel;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class Export
{
    private $excelObj;
    private $addActiveSheetIndex = 0;

    public function __construct()
    {
        $this->excelObj = new Spreadsheet();
    }


    public function makeSheet($sheelName, $titleConfig, $dataList)
    {
        $excel = $this->excelObj;

        if($this->addActiveSheetIndex > 0){
            $excel->createSheet();
        }
        $actionSheetIndexKey = $this->addActiveSheetIndex++;
        $excel->setActiveSheetIndex($actionSheetIndexKey);
        $sheet = $excel->getActiveSheet();  //获取当前操作sheet的对象
        $sheet->setTitle($sheelName);  //设置当前sheet的标题

        $fieldNum = 0;
        foreach($titleConfig as $fieldName)
        {

            $keyName = $this->getKeyName($fieldNum);
            //设置宽度为true,不然太窄了
            $excel->getActiveSheet()->getColumnDimension($keyName)->setAutoSize(true);
            // 设置标题字段
            $sheet->setCellValue("{$keyName}1", $fieldName);

            $fieldNum++;
        }

        // 设置内容

        foreach ($dataList as $key => $value) {
            $row = $key + 2;
            $fieldNum = 0;
            foreach($titleConfig as $field => $fieldName)
            {
                $keyName = $this->getKeyName($fieldNum);
                $sheet->setCellValue("{$keyName}{$row}", $value[$field]);
                $fieldNum++;
            }
        }

//        $this->downloadExcel($excel, $fileName, 'Xls');
    }

    //公共文件，用来传入xls并下载
    public function downloadExcel($filename, $format = "Xls")
    {
        $excel = $this->excelObj;

        // $format只能为 Xlsx 或 Xls
        if ($format == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } elseif ($format == 'Xls') {
            header('Content-Type: application/vnd.ms-excel');
        }

        $fieldFormat = strtolower($format);
        header("Content-Disposition: attachment;filename={$filename}.{$fieldFormat}");
        header('Cache-Control: max-age=0');
        $objWriter = IOFactory::createWriter($excel, $format);

        $objWriter->save('php://output');

        //通过php保存在本地的时候需要用到
        //$objWriter->save($dir.'/demo.xlsx');

        //以下为需要用到IE时候设置
        // If you're serving to IE 9, then the following may be needed
        //header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        //header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        //header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        //header('Pragma: public'); // HTTP/1.0
        exit;
    }



    /**
     * 根据key值获取对应的列标名称
     * @param $key
     * @return mixed|string
     */
    public function getKeyName($key)
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