<?php

/**
 * 快速排序算法
 *
 * @param array $arr 待排序的数组
 * @param int|null $left 左边界索引，默认为数组的起始位置
 * @param int|null $right 右边界索引，默认为数组的末尾位置
 */
function quickSort(array &$arr, ?int $left = null, ?int $right = null) {
    if ($left === null) {
        $left = 0;
    }
    if ($right === null) {
        $right = count($arr) - 1;
    }
    if ($left >= $right) {
        // 当左边界大于等于右边界时，表示已经完成排序，退出递归
        return;
    }

    $i = $left - 1;
    $j = $right + 1;
    $pivot = $arr[($left + $right) >> 1]; // 选择中间位置的元素作为基准值（枢纽元）

    while ($i < $j) {
        // 在左侧找到第一个大于等于基准值的元素
        do {
            $i++;
        } while ($arr[$i] < $pivot);

        // 在右侧找到第一个小于等于基准值的元素
        do {
            $j--;
        } while ($arr[$j] > $pivot);

        if ($i < $j) {
            // 交换两个元素的位置
            $temp = $arr[$i];
            $arr[$i] = $arr[$j];
            $arr[$j] = $temp;
        }
    }

    // 对基准值左侧的子数组进行递归排序
    quickSort($arr, $left, $j);

    // 对基准值右侧的子数组进行递归排序
    quickSort($arr, $j + 1, $right);
}

// 测试
$a = [3, 1, 5, 2, 4];
quickSort($a);
echo "排序结果：" . implode(", ", $a);