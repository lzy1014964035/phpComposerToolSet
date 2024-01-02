<?php

namespace ToolSet\Service\Other;

use Overtrue\Pinyin\Pinyin;
use ToolSet\Service\ServiceBase;

class ServicePinYin extends ServiceBase
{
    private $pinyinObject;

    public function __construct()
    {
        $this->pinyinObject = new Pinyin();
    }

    // 获取拼音原始数组
    public function getArray($string)
    {
        return $this->pinyinObject->convert($string);
    }

    // 获取拼音原始数组（带声调的）
    public function getToneArray($string)
    {
        return $this->pinyinObject->convert($string, PINYIN_TONE);
    }

    // 获取拼音字符串
    public function getLinkString($string, $linkSign = '')
    {
        return $this->pinyinObject->permalink($string, $linkSign);
    }

    // 获取拼音首字母字符串 
    public function getAbbrString($string, $linkSign = '')
    {
        return $this->pinyinObject->abbr($string, $linkSign);
    }


}