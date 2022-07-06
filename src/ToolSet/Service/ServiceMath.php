<?php


namespace ToolSet\Service;


trait ServiceMath
{
    /**
     * 相除
     * @param $one
     * @param $two
     * @param int $roundNum
     * @return float|int
     */
    public static function beDividedBy($one, $two, $roundNum = 0)
    {
        if ($one == 0 || $two == 0) {
            $res = 0;
        } else {
            $res = $one / $two;
        }
        if ($roundNum != 0) {
            $res = round($res, $roundNum);
        }
        return $res;
    }


}