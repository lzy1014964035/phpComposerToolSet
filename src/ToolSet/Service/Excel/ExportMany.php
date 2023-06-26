<?php

namespace ToolSet\Service\Excel;

use ToolSet\Service\ServiceBase;

class ExportMany
{
    private $excelObject;
    private $fieldName;

    public function __construct($fieldName)
    {
        $this->excelObject = new Export();
        $this->fieldName = $fieldName;
    }

    /**
     * 创建sheet页
     * @param $sheetName // sheet页名称
     * @param $titleConfig // 表头配置 例如 ['name' => '用户名', 'phone' => '手机号',...]
     * @param $dataList // 内容列表 例如 [['name' => '张三', 'phone' => '13100000000'],['name' => '李四', 'phone' => '1320000000'],...]
     * @return Export
     */
    public function makeSheet($sheetName, $titleConfig, $dataList, $otherParam = [])
    {
        $this->excelObject->makeSheet($sheetName, $titleConfig, $dataList, $otherParam);
        return $this->excelObject;
    }

    /**
     * 下载
     */
    public function download($otherParam = [])
    {
        $setFieldName = $this->fieldName;
        if (ServiceExcel::$fileHasDate == true) {
            $setFieldName = $this->fieldName . ServiceBase::getYmdDate();
        }
        if (ServiceExcel::$fileHasTime == true) {
            $setFieldName = $this->fieldName . ServiceBase::getYmdHisDate();
        }
        $result = null;
        if(isset($otherParam['is_save_with_last_path']) && $otherParam['is_save_with_last_path'] === true){
            $this->excelObject->saveFileToPath();
            return $result;
        }
        $this->excelObject->downloadExcel($setFieldName);
        return $result;
    }

}