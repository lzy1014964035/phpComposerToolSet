<?php


namespace lzy1014964035\PHPComposerToolSet\Service;


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
            $proportion = BaseService:: beDividedBy($num, $sumNum) * 100;
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
}