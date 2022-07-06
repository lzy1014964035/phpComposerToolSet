<?php


namespace ToolSet\Service;


trait ServiceTime
{
    /**
     * 获取 YmdHis格式的日期
     * @param string $time
     * @return false|string
     */
    public static function getYmdHisDate($time = "")
    {
        $returnDate = !empty($time) ? date('Y-m-d H:i:s', $time) : date("Y-m-d H:i:s");
        return $returnDate;
    }

    /**
     * 获取Ymd格式的date
     * @param $date
     * @return false|string
     */
    public static function getYmdDate($date = "")
    {
        if ((string)(int)$date != $date) {
            $date = strtotime($date);
        }

        $returnDate = $date ? date('Y-m-d', $date) : date('Y-m-d');
        return $returnDate;
    }

    /**
     * 获取Y格式的date
     * @param $date
     * @return false|string
     */
    public static function getYDate($date)
    {
        if ((string)(int)$date != $date) {
            $date = strtotime($date);
        }

        $returnDate = $date ? date('Y', $date) : date('Y');
        return $returnDate;
    }


    /***
     * 返回两个时间内所有的时间集合 单位：天
     * @param $startTime // 开始时间：2020-04-01
     * @param $endTime // 结束时间：2020-04-20
     * @return array      返回集合列表
     */
    public static function getDateList($startTime, $endTime)
    {
        if (strtotime($startTime) <= strtotime($endTime)) {
            $dayList = [];
            do {
//                向dayList尾部添加日期
                array_push($dayList, date('Y-m-d', strtotime($startTime)));
//               计算下次添加日期
                $startTime = date("Y-m-d", strtotime("+1 day", strtotime($startTime)));
//                判读条件
            } while (strtotime($startTime) <= strtotime($endTime));
//                返回结果
            return $dayList;

        } else {
            return [];
        }
    }

    /**
     * 获取一个月份格式的时间
     * @param $dateOrTime // 可能是date格式 也可能是时间戳
     * @return false|string
     */
    public static function makeMonthDate($dateOrTime)
    {
        $time = strtotime($dateOrTime);
        $time = $time ?: $dateOrTime;
        $date = Date('Y-m', $time);
        return $date;
    }

    /**
     * 获取两个日期（用于between操作）
     * @param $twoDate
     * @return array
     */
    public static function getTwoDateArray($twoDate)
    {
        if (empty($twoDate)) {
            return [];
        }

        if (is_array($twoDate)) {
            return [
                $twoDate[0],
                $twoDate[count($twoDate) - 1],
            ];
        }

        $array = explode(' - ', $twoDate);
        if (!strtotime($array[0])) {
            return [];
        }
        if (count($array) < 2) {
            $array[] = $array[0];
        }


        return $array;
    }


    /**
     * 获取一年的全部月份
     * @param $year
     * @return array
     */
    public static function getYearMonthArray($year)
    {
        $monthArray = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = $i < 10 ? "0{$i}" : $i;
            $monthArray[] = "{$year}-{$month}";
        }
        return $monthArray;
    }

    /**
     * 获取一年中小于等于指定月之前的月份（用预算年指定月累计）
     * @param $month
     * @return array
     */
    public static function getYearBeforeMonthArray($month)
    {
        $year = self::getDateYearString($month);
        $yearMonthArray = self::getYearMonthArray($year);

        $monthTime = strtotime($month);
        $monthArray = [];
        foreach ($yearMonthArray as $setMonth) {
            if (strtotime($setMonth) <= $monthTime) {
                $monthArray[] = $setMonth;
            }
        }
        return $monthArray;
    }

    /**
     * 获取对应月最后一天
     * @param $month
     * @return false|string
     */
    public static function getMonthLastDate($month)
    {
        $time = strtotime($month . " +1 month -1 day");
        $date = self::getYmdDate($time);
        return $date;
    }


    /**
     * 获取设置的月
     * @param $monthArray
     * @return false|mixed|string
     */
    public static function getSetMonth($monthArray)
    {
        $nowMonth = date('Y-m');
        // 默认查询月为当前月
        $setMonth = $nowMonth;
        if (!empty($monthArray)) {
            // 如果当前月小于查询区间的最小月，那么查询区间的最小月就为查询月
            if (strtotime($nowMonth) < strtotime($monthArray[0])) {
                $setMonth = $monthArray[0];
                // 如果当前月大约查询区间的最大余额，那么查询区间的最大月为查询月
            } elseif (strtotime($nowMonth) > strtotime($monthArray[count($monthArray) - 1])) {
                $setMonth = $monthArray[count($monthArray) - 1];
            }
        }

        return $setMonth;
    }


    /**
     * 获取内容中的所有为月份的key
     * @param $data
     * @return array
     */
    public static function getDataMonthKeyArray($data)
    {
        $monthArray = [];
        foreach ($data as $key => $value) {
            if (strtotime($key) > 0) {
                $monthArray[] = $key;
            }
        }
        return $monthArray;
    }

    /**
     * 获取两个Month之间的所有月份的数组
     * @param null $monthOne
     * @param null $monthTwo
     * @return array
     */
    public static function getTwoMonthBetweenMonthArray($monthOne = null, $monthTwo = null)
    {
        if (is_array($monthOne)) {
            $monthTwo = $monthOne[count($monthOne) - 1];
            $monthOne = $monthOne[0];
        }
        $monthOne = strtotime($monthOne);
        $monthOne = date("Y-m", $monthOne);
        $monthTwo = strtotime($monthTwo);
        $monthTwo = date("Y-m", $monthTwo);

        $returnArray = [];

        while (true) {
            if (strtotime($monthOne) <= strtotime($monthTwo)) {
                $returnArray[] = $monthOne;
                $monthOne = strtotime("{$monthOne} +1 month");
                $monthOne = date("Y-m", $monthOne);
            } else {
                break;
            }
        }

        return $returnArray;
    }

    /**
     * 获取所属季度中至此的月份
     * @param $month
     * @return array
     */
    public static function getQuarterWithNowMonth($month)
    {
        $quarter = self::getMonthQuarter($month);
        $year = self::getDateYearString($month);
        $monthArray = self::getQuarterMonths($year, $quarter);
        foreach ($monthArray as $key => $monthString) {
            if (strtotime($monthString) > strtotime($month)) {
                unset($monthArray[$key]);
            }
        }
        return $monthArray;
    }

    /**
     * 获取一个季度拥有的月份
     * @param $year
     * @param $quarter
     * @return array
     */
    public static function getQuarterMonths($year, $quarter)
    {
        $quarterConfig = [
            1 => [
                '01',
                '02',
                '03'
            ],
            2 => [
                '04',
                '05',
                '06'
            ],
            3 => [
                '07',
                '08',
                '09'
            ],
            4 => [
                '10',
                '11',
                '12'
            ],
        ];
        $quarterConfigData = $quarterConfig[$quarter];
        $monthArray = [];
        foreach ($quarterConfigData as $monthName) {
            $monthArray[] = "{$year}-{$monthName}";
        }
        return $monthArray;
    }

    /**
     * 获取月份所属的季度
     * @param $month
     * @return mixed
     */
    public static function getMonthQuarter($month)
    {
        $month = strtotime($month);
        $month = date("m", $month);
        $month = (int)$month;
        $quarterConfig = [
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 2,
            7 => 3,
            8 => 3,
            9 => 3,
            10 => 4,
            11 => 4,
            12 => 4,
        ];
        return $quarterConfig[$month];
    }

    /**
     * 获取一个月份所在季度拥有的月份
     * @param $month
     * @return array|bool
     */
    public static function getMonthQuarterMonths($month)
    {
        if (!strtotime($month)) {
            return false;
        }
        $year = explode('-', $month)[0];
        $quarter = self::getMonthQuarter($month);
        $months = self::getQuarterMonths($year, $quarter);
        return $months;
    }

    /**
     * 获取一个时间段内的每周的时间组
     * @param string $startTime
     * @param string $endTime
     * @param bool $hasWeekend
     * @return array
     */
    public static function getWeekDateArray(string $startTime, string $endTime, $hasWeekend = true)
    {
        $dayS = 86400;
        $time = strtotime($startTime);
        $weekArray = [[]];
        $weekDaysArray = [7, 1, 2, 3, 4, 5, 6];
        while ($time <= strtotime($endTime)) {
            $weekDay = date('w', $time);
            $weekDay = $weekDaysArray[$weekDay];
            if (($hasWeekend == true) || ($hasWeekend == false && $weekDay < 6)) {
                $weekArray[count($weekArray) - 1][] = self::getYmdDate($time);
            }

            if ($weekDay >= 7) {
                $weekArray[] = [];
            }
            $time += $dayS;
        }
        foreach ($weekArray as $key => $value) {
            if (empty($value)) {
                unset($weekArray[$key]);
            }
        }
        return $weekArray;
    }


}