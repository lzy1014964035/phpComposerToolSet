<?php

function findMax($arr) {
    $n = count($arr);

    // 基本情况，数组为空时直接返回
    if ($n == 0) {
        return null;
    }

    // 当只有一个元素时，直接返回该元素
    if ($n == 1) {
        return $arr[0];
    }

    // 分解：将数组分成两半
    $mid = (int)($n / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    // 解决：递归地求解左半部分和右半部分的最大值
    $maxLeft = findMax($left);
    $maxRight = findMax($right);

    // 合并：返回左半部分和右半部分的最大值
    return max($maxLeft, $maxRight);
}

// 测试
$array = [2, 6, 1, 8, 4, 5];
$max = findMax($array);
echo "最大值：" . $max;


echo "\r\n-------------------------\r\n";
function findMaxDiy($array)
{
    $count = count($array);
    // 切片数量小于指定值时，直接进行处理
    if($count < 5){
        return max($array);
    }

    // 按照数据大小，切成10等份
    $pageMax = $count > 10 ? 10 : $count;
    $limit = (int)($count / $pageMax);
//    var_dump($pageMax, $limit, $count);die;
    // 根据数据进行切片，减少注入下一个方法的数据量，降低栈内存的占用
    $sliceArray = [];
    for($page = 1; $page <= $pageMax; $page++){
        $offset = ($page - 1) * $limit;
        $slice = ($page === $pageMax) ? array_slice($array, $offset) : array_slice($array, $offset, $limit);
        $sliceArray[] = $slice;
    }

    // 处理每个更小的切片
    $resultArray = [];
    foreach($sliceArray as $slice){
        $resultArray[] = findMaxDiy($slice);
    }

    // 合并：返回左半部分和右半部分的最大值
    return max($resultArray);
}
// 测试
$array = [
    2, 6, 1, 8, 4, 5, 9, 5, 6, 1, 2, 3,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 12
];
$max = findMaxDiy($array);
echo "最大值：" . $max;