<?php

function knapsack($values, $weights, $capacity) {
    // 获取物品数量
    $n = count($values);
    // 创建动态规划数组
    $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));

    // 开始动态规划计算
    for ($dataKey = 1; $dataKey <= $n; $dataKey++) {
        for ($wNum = 1; $wNum <= $capacity; $wNum++) {
            // 如果当前物品重量小于等于当前背包容量
            if ($weights[$dataKey - 1] <= $wNum) {
                // 尝试将当前物品放入背包，并计算总价值
                // 选择放入当前物品的价值与不放入当前物品的价值中的最大值
                $dp[$dataKey][$wNum] = max(
                    $dp[$dataKey - 1][$wNum], // 旧数据
                    $values[$dataKey - 1] + $dp[$dataKey - 1][$wNum - $weights[$dataKey - 1]] //  当前数据
                );
            } else {
                // 当前物品重量大于当前背包容量，无法放入，保持不变
                $dp[$dataKey][$wNum] = $dp[$dataKey - 1][$wNum];
            }
        }
    }
//    dd($dp);
    // 返回背包中物品的最大总价值
    return $dp[$n][$capacity];
}

// 测试
$values = [70, 100, 165, 70]; // 物品的价值
$weights = [10, 20, 30, 10]; // 物品的重量
$capacity = 40; // 背包的容量
$maxValue = knapsack($values, $weights, $capacity);
echo "背包中物品的最大总价值：" . $maxValue;



function knapsackDiy($values, $weights, $capacity)
{
    // 获取物品的数量
    $dataNum = count($values);
    // 生成二维填充组
    $dataPack = array_fill(0, $dataNum, array_fill(0, $capacity, 0));

    for($dataKey = 0; $dataKey <= $dataNum; $dataKey++)
    {
        for($packWeightKey = 0; $packWeightKey <= $capacity; $packWeightKey++)
        {
            // 如果背包重量键为0，那么就跳过
            if($packWeightKey == 0){
                $dataPack[$dataKey][$packWeightKey] = $dataPack[$dataKey - 1][$capacity - 1];
                continue;
            }
            // 对比当前背包的重量物品的重量
            if($packWeightKey >= $weights[$dataKey]){
                // 上一个物品价值
                $last = $dataPack[$dataKey - 1][$packWeightKey - 1];
                // 当次物品价值
                $now = $values[$dataKey - 1] + $dataPack[$dataKey][$packWeightKey - $weights[$dataKey]];
                $dataPack[$dataKey][$packWeightKey] = max($last, $now);
            }else{
                $dataPack[$dataKey][$packWeightKey]  = $dataPack[$dataKey][$packWeightKey - 1];
            }
        }
    }

    return $dataPack[$dataNum][$capacity];
}

echo "\r\n---------------------------------------------\r\n";
// 测试
$maxValue = knapsackDiy($values, $weights, $capacity);
echo "背包中物品的最大总价值：" . $maxValue;