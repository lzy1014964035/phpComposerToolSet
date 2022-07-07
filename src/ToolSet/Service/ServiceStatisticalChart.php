<?php


namespace ToolSet\Service;


trait ServiceStatisticalChart
{
    /**
     * 获取饼状图比例数据
     * @param $data
     * @return array
     */
    public static function makePieProportion($data)
    {
        $setNum = 100;
        $sumNum = array_sum($data);
        $returnData = [];
        foreach ($data as $key => $num) {
            $proportion = ServiceMath:: beDividedBy($num, $sumNum) * 100;
            $setNum -= $proportion;
            $returnData[$key] = $proportion;
        }

        if ($setNum > 0) {
            foreach ($returnData as &$value) {
                if ($value > 0) {
                    $value += $setNum;
                    break;
                }
            }
        }

        return $returnData;
    }

    /**
     * 处理成 折线图/柱状列表图 的数据
     * @param array $listData // [['name' => '张三', 'money' => '120'],['name' => '李四', 'money' => '150'],['name' => '王五', 'money' => '160']]
     * @param array $fieldArray // ['name' => ['张三', '李四', '王五'], 'money' => [120, 150, 160]]
     * @return array
     */
    public static function makeStatisticalChartData(array $listData, $fieldArray = [])
    {
        if(empty($listData) || !is_array($listData[0])){
            return [];
        }

        if(empty($fieldArray)){
            $firstData = $listData[0];
            $fieldArray = array_keys($firstData);
        }
        $count = count($listData);

        $returnList = [];
        for($key = 0; $key < $count; $key++){
            foreach($fieldArray as $field){
                $returnList[$field][$key] = ServiceBase::emptyDefault($listData[$key][$field], null);
            }
        }

        return $returnList;
    }


}