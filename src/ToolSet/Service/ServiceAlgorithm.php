<?php

namespace ToolSet\Service;


trait ServiceAlgorithm
{
    /**
     * 求笛卡尔积
     * @param array $array
     * @return array
     */
    public static function makeCartesianProduct(array $array)
    {
        $returnArray = [];
        foreach($array as $field => $fieldValueArray)
        {
            if(empty($returnArray)){
                foreach($fieldValueArray as $fieldValue)
                {
                    $returnArray[] = [$field => $fieldValue];
                }
            }else{
                $setReturnArray = [];
                foreach($returnArray as $saveArray)
                {
                    foreach($fieldValueArray as $fieldValue)
                    {
                        $setSaveArray = $saveArray;
                        $setSaveArray[$field] = $fieldValue;
                        $setReturnArray[] = $setSaveArray;
                    }
                }
                $returnArray = $setReturnArray;
            }
        }
        return $returnArray;
    }
}