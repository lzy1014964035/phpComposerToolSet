<?php

namespace ToolSet\Service\Excel;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Color;
use \PhpOffice\PhpSpreadsheet\Cell\DataType;

class Export
{
    private $excelObj;
    private $addActiveSheetIndex = 0;

    public function __construct()
    {
        $this->excelObj = new Spreadsheet();
    }


    /**
     * 创建sheet页
     * @param $sheetName // sheet页名称
     * @param $titleConfig // 表头配置 例如 ['name' => '用户名', 'phone' => '手机号',...]
     * @param $dataList // 内容列表 例如 [['name' => '张三', 'phone' => '13100000000'],['name' => '李四', 'phone' => '1320000000'],...]
     * @param $otherParam   // 其他参数
     *                      autoMergeField 需要自动合并的字段
     *                      listOffsetNum  内容偏移行数（内容向下偏移，将上面空出来方便插入一些数据）
     *                      appointItemArray 填充指定格内容(比如在表头插个时间，插个导出人之类的)
     *                                  value 值
     *                                  merge 自动合并的参数
     *                                  levelPosition 位置（ left 居左，center 居中，right 居右）
     *                                  fontColor  字体颜色  'FF0000' 用rgb255
     *                      setLineHeight  设置指定行的高度
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function makeSheet($sheetName, $titleConfig, $dataList, $otherParam)
    {
        $autoMergeData = [];
        $autoMergeField = isset($otherParam['autoMergeField']) ? $otherParam['autoMergeField'] : null;
        $listOffsetNum = isset($otherParam['listOffsetNum']) ? $otherParam['listOffsetNum'] : 0;
        $appointItemArray = isset($otherParam['appointItemArray']) ? $otherParam['appointItemArray'] : [];
        $setLineHeight = isset($otherParam['setLineHeight']) ? $otherParam['setLineHeight'] : [];


        $excel = $this->excelObj;

        if($this->addActiveSheetIndex > 0){
            $excel->createSheet();
        }
        $actionSheetIndexKey = $this->addActiveSheetIndex++;
        $excel->setActiveSheetIndex($actionSheetIndexKey);
        $sheet = $excel->getActiveSheet();  //获取当前操作sheet的对象
        $sheet->setTitle($sheetName);  //设置当前sheet的标题


        $widthData = [];
        $fieldNum = 0;
        foreach($titleConfig as $fieldName)
        {
            $keyName = ServiceExcel::getKeyName($fieldNum);
            $widthData[$fieldNum] = strlen($fieldName);
            $row = 1 + $listOffsetNum;
            // 设置标题字段
            $sheet->setCellValue("{$keyName}{$row}", $fieldName);
            $fieldNum++;
        }

        // 设置居中
        if(ServiceExcel::$dataAllCenter){
            $sheet->getStyle("A:ZZ")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);
        }

        // 设置自动换行
        $sheet->getStyle('A:ZZ')->getAlignment()->setWrapText(true);

        // 设置内容
        foreach ($dataList as $key => $value) {
            $row = $key + 2 + $listOffsetNum;
            $fieldNum = 0;
            foreach($titleConfig as $field => $fieldName)
            {
                $keyName = ServiceExcel::getKeyName($fieldNum);

                $valueLen = strlen($value[$field]);
                $widthData[$fieldNum] = $widthData[$fieldNum] > $valueLen ? $widthData[$fieldNum] : $valueLen;

                $key = "{$keyName}{$row}";
                $sheet->setCellValue($key, $value[$field]);
                $fieldNum++;

                // 如果需要自动合并就记录KEY
                if($autoMergeField && in_array($field, $autoMergeField)){
                    $autoMergeData[$field][$key] = $value[$field];
                }
            }
        }

        // 自动设置宽度
        foreach($widthData as $fieldNum => $length)
        {
            $sheet->getColumnDimensionByColumn($fieldNum + 1)->setWidth($length + 4);
        }

        // 设置自定义参数
        if($appointItemArray){
            $this->setAppointItem($sheet, $appointItemArray);
        }

        // 自动合并
        if($autoMergeData){
            $this->setAutoMerge($sheet, $autoMergeData);
        }

        // 设置行高度
        if($setLineHeight)
        {
            foreach($setLineHeight as $lineKey => $height)
            {
                $sheet->getRowDimension($lineKey)->setRowHeight($height);
            }
        }

    }

    /**
     * 设置自动合并
     * @param $sheet
     * @param $autoMergeData
     */
    private function setAutoMerge($sheet, $autoMergeData)
    {
        foreach($autoMergeData as $field => $keyArray){
            $oneKey = null;
            $twoKey = null;
            foreach($keyArray as $key => $value){
                // 检查如果为初始化，就赋予默认值
                if(empty($oneKey)){
                    $oneKey = $twoKey = $key;
                }
                // 如果后续的与前面的相同，就赋予下标
                if($keyArray[$twoKey] == $value){
                    $twoKey = $key;
                }else{
                    // 反之，就表示当前数据与前面的不符合，前面的需要合并
                    // 如果前面只有一条，两个下标一样，那就不用合并了
                    if($oneKey != $twoKey){
                        $sheet->mergeCells("{$oneKey}:{$twoKey}");
                    }
                    // 之后初始化下标
                    $oneKey = $twoKey = $key;
                }
            }
            // 循环结束后，进行一次检查
            if($oneKey != $twoKey){
                $sheet->mergeCells("{$oneKey}:{$twoKey}");
            }
        }
    }


    /**
     * 设置自定义参数
     * @param $sheet
     * @param $appointItemArray
     */
    private function setAppointItem($sheet, $appointItemArray)
    {
        // 设置自定义内容
        foreach($appointItemArray as $key => $data)
        {
            # todo 这里不好做自动设置宽度的处理，暂时没空优化，后续有时间了，想法优化一下
            if(!is_array($data)){
                $value = $data;
                $sheet->setCellValue($key, $value);
            }else{
                $value = isset($data['value']) ? $data['value'] : null;
                $merge = isset($data['merge']) ? $data['merge'] : null;
                $levelPosition = isset($data['levelPosition']) ? $data['levelPosition'] : null;
                $fontColor = isset($data['fontColor']) ? $data['fontColor'] : null;
                // 字段合并
                if($merge){
                    if(is_string($merge)){
                        $sheet->mergeCells($merge);
                    }
                    if(is_array($merge)){
                        $startKeyNum = $merge['startKeyNum']; // 开始的key的下标
                        $startLine = $merge['startLine']; // 开始的行数
                        $endKeyNum = $merge['endKeyNum']; // 结束的key的下标
                        $endLine = $merge['endLine']; // 结束的行数

                        $startKey = ExcelService::getKeyName($startKeyNum - 1);
                        $endKey = ExcelService::getKeyName($endKeyNum - 1);

                        $sheet->mergeCells("{$startKey}{$startLine}:{$endKey}{$endLine}");
                    }
                }

                $levelPositionConfig = [
                    'left' => Alignment::HORIZONTAL_LEFT,
                    'center' => Alignment::HORIZONTAL_CENTER,
                    'right' => Alignment::HORIZONTAL_RIGHT,
                ];

                $levelPositionValue = $levelPositionConfig[$levelPosition] ?? null;
                if($levelPositionValue){
                    $sheet->getStyle("{$key}:{$key}")->applyFromArray([
                        'alignment' => [
                            'horizontal' => $levelPositionValue,
                        ]
                    ]);
                }

                // 设置值
                if($value !== null){
                    $sheet->setCellValue($key, $value);
                }


                // 设置颜色
                if($fontColor){
                    $sheet->getStyle($key)->getFont()->setColor(new Color($fontColor));
                }
            }
        }
    }

    //公共文件，用来传入xls并下载
    public function downloadExcel($filename)
    {
        $excel = $this->excelObj;
        $format = ServiceExcel::$fileFormat;
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

    public function saveFileToPath()
    {
        $format = ServiceExcel::$fileFormat;
        $savePath = ServiceExcel::$fileSavePath;
        $saveFilePath = ServiceExcel::$lastFileSavePath;
        if( ! is_dir($savePath)){
            mkdir($savePath, 0777 ,true);
        }
        if(empty($saveFilePath)){
            throw new \Exception('保存excel文件失败，请先设置保存的路径');
        }
        // 导出后清空
        ServiceExcel::$lastFileSavePath = null;
        $excel = $this->excelObj;
        $objWriter = IOFactory::createWriter($excel, $format);
        $objWriter->save($saveFilePath);
        return $saveFilePath;
    }


}