<?php

$chineseCharacter = "我";
var_dump([
    $chineseCharacter, $chineseCharacter[0]
]);
$firstByte = mb_substr($chineseCharacter, 0, 1, 'UTF-8');
$asciiValue = ord($firstByte);
var_dump([
    $asciiValue, $firstByte, $firstByte[0], hexdec('0x1')
]);

// 前缀 "0x" 表示十六进制，例如 0x1A 表示数值 26。
//没有前缀的数值默认为十进制，例如 123 表示数值 123。
//前缀 "0" 表示八进制，例如 012 表示数值 10。
//前缀 "0b" 表示二进制，例如 0b101 表示数值 5。
//这些前缀用于标识不同进制的数值，有助于在代码中明确指定数值的进制。再次感谢您的准确总结！如有其他问题，请随时提问。


