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
     * @param $sheetName
     * @param $titleConfig
     * @param $dataList
     * @return Export
     */
    public function makeSheet($sheetName, $titleConfig, $dataList)
    {
        $this->excelObject->makeSheet($sheetName, $titleConfig, $dataList);
        return $this->excelObject;
    }

    /**
     * @return Export
     */
    public function download()
    {
        $setFieldName = $this->fieldName;
        if(ServiceExcel::$fileHasDate == true){
            $setFieldName = $this->fieldName . ServiceBase::getYmdDate();
        }
        if(ServiceExcel::$fileHasTime == true){
            $setFieldName = $this->fieldName . ServiceBase::getYmdHisDate();
        }
        $this->excelObject->downloadExcel($setFieldName);
        return $this->excelObject;
    }

}