<?php


namespace ToolSet\Service;


trait ServiceString
{
    public static $transformationDecimals = 2;

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

    /**
     * 搜索前后缀之间的字符串
     * @param $string
     * @param $prefix
     * @param $suffix
     * @return string
     */
    public static function searchStringWithPS($string, $prefix, $suffix)
    {
        $start = strpos($string, $prefix);
        if ($start === false) {
            return false;
        }
        $start = $start + strlen($prefix);
        $end = strpos($string, $suffix, $start);
        if ($end === false) {
            return false;
        }
        $searchString = substr($string, $start, $end - $start);
        return $searchString;
    }

    /**
     * 获取对应的头部内容
     * @param $string
     * @param $headerStrArray
     * @return string
     */
    public static function getHeaderString($string, $headerStrArray)
    {
        $returnString = "";
        $len = strlen($string);
        $num = 0;
        while ($num < $len) {
            $str = $string[$num];
            if (in_array($str, $headerStrArray)) {
                $returnString .= $str;
            } else {
                break;
            }
            $num++;
        }
        return $returnString;
    }

    /**
     * 字符串转化为数字
     * @param $number
     * @param null $decimals
     * @return float|int
     */
    public static function formatToNum($number, $decimals = null)
    {
        if ($decimals === null) {
            $decimals = self::$transformationDecimals;
        }

        $str = trim($number);
        if (empty($str)) return 0;

        $str = str_replace(',', '', $str);

        if (strpos($str, '%') !== false) {
            $str = str_replace('%', '', $str);
        }
        if (strpos($str, '万元') !== false) {
            $str = str_replace('万元', '', $str);
        }

        if (!is_numeric($str) && strpos($str, '-') !== false) return 0;
        if (strpos($str, '#') !== false) return 0;
        if (strpos($str, "/") !== false) return 0;

        // 如果是负1就向下取整
        if ($decimals == -1) {
            $num = floor($str);
        } else {
            $num = round($str, $decimals);
        }

        return $num;
    }

    public static function numToFormat($number, $decimals = null)
    {
        if ($decimals === null) {
            $decimals = self::$transformationDecimals;
        }
        // 如果是负1就向下取整
        if ($decimals == -1) {
            return floor($number);
        }
        if (!empty($number) && is_numeric($number)) {
            return number_format($number, $decimals);
        }
        return 0;
    }

}