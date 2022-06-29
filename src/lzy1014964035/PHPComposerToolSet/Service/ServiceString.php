<?php


namespace lzy1014964035\PHPComposerToolSet\Service;


trait ServiceString
{

    /**
     * 中文标识转英文处理
     * @param $string
     * @return mixed
     */
    public static function chineseToEnglishProcessing($string)
    {
        $string = str_replace('，', ',', $string);
        $string = str_replace('（', '(', $string);
        $string = str_replace('）', ')', $string);
        return $string;
    }

    /**
     * 判断字符串是否有对应的前缀
     * @param string $string
     * @param string $checkString
     * @return bool
     */
    public static function checkStringHasPrefix(string $string, string $checkString)
    {
        if (strpos($string, $checkString) === 0) {
            return true;
        }
        return false;
    }


    /**
     * 判断字符串是否有对应的后缀
     * @param string $string
     * @param string $checkString
     * @return bool
     */
    public static function checkStringHasSuffix(string $string, string $checkString)
    {
        $position = strpos($string, $checkString);
        if ($position > -1 && $position == (strlen($string) - strlen($checkString))) {
            return true;
        }
        return false;
    }

    /**
     * 获取对应级别空格的string
     * @param string $string
     * @param int $level
     * @param string $setString
     * @return string
     */
    public static function getLevelString(string $string, int $level, $setString = "　")
    {
        while (true) {
            if ($level < 2) {
                return $string;
            }
            $level--;
            $string = "{$setString}{$string}";
        }
    }


    /**
     * 情况对应级别空格的string
     * @param string $string
     * @return string
     */
    public static function clearLevelString(string $string)
    {
        return str_replace('　', '', $string);
    }

    /**
     * 获取日期中的年的字符串
     * @param $date
     * @return mixed
     */
    public static function getDateYearString($date)
    {
        $string = $date;
        $string = explode('-', $string);
        return $string[0];
    }

    /**
     * 获取日期中的月的字符串
     * @param $date
     * @param bool $monthToString
     * @return mixed
     */
    public static function getDateMonthString($date, $monthToString = false)
    {
        $string = $date;
        $string = explode('-', $string)[1];
        if ($string[0] == "0" && $monthToString === false) {
            $string = str_replace('0', '', $string);
        }
        return $string;
    }

    /**
     * 获取日期中的日的字符串
     * @param $date
     * @return mixed
     */
    public static function getDateDayString($date)
    {
        $string = $date;
        $string = explode('-', $string);
        $string = str_replace('0', '', $string[2]);
        return $string;
    }

}