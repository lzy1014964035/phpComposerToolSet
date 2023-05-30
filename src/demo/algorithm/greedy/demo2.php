<?php
function makeChange($amount, $coins)
{
    // 对硬币面额按降序排序
    rsort($coins);

    $change = array(); // 存储找零的硬币组合

    foreach ($coins as $coin) {
        while ($amount >= $coin) {
            // 找到一个可用的硬币面额
            $change[] = $coin;
            $amount -= $coin;
        }
    }

    if ($amount > 0) {
        // 无法凑出指定金额
        return "无法凑出指定金额的硬币组合";
    }

    return $change;
}

// 示例用法
$amount = 49;
$coins = array(10, 20, 5, 1);

$result = makeChange($amount, $coins);

if (is_array($result)) {
    echo "找零{$amount}元的最少硬币组合为：" . implode(', ', $result);
} else {
    echo $result;
}