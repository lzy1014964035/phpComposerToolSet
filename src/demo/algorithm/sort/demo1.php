<?php

function quickSort(&$a, $l = null, $r = null) {
    if($l === null){
        $l = 0;
    }
    if($r === null){
        $r = count($a) - 1;
    }
    if ($l >= $r) {
        return;
    }
    $i = $l - 1;
    $j = $r + 1;
    $pivot = $a[($l + $r) >> 1];
    while ($i < $j) {
        do {
            $i++;
        } while ($a[$i] < $pivot);
        do {
            $j--;
        } while ($a[$j] > $pivot);
        if ($i < $j) {
            $temp = $a[$i];
            $a[$i] = $a[$j];
            $a[$j] = $temp;
        }
    }
    quickSort($a, $l, $j);
    quickSort($a, $j + 1, $r);
}

// 测试
$a = [3, 1, 5, 2, 4];
quickSort($a);
echo "排序结果：" . implode(", ", $a);