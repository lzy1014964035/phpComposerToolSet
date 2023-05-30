<?php
function combinationSum($candidates, $target) {
    $result = [];
    $combination = [];
    backtrack($candidates, $target, 0, $combination, $result);
    return $result;
}

function backtrack($candidates, $target, $start, &$combination, &$result) {
    if ($target < 0) {
        // 当目标值小于0时，不符合条件，终止递归
        return;
    } elseif ($target === 0) {
        // 当目标值等于0时，找到一组满足条件的组合，加入结果集
        $result[] = $combination;
        return;
    }

    for ($i = $start; $i < count($candidates); $i++) {
        // 将当前数字加入组合
        $combination[] = $candidates[$i];

        // 继续搜索下一个位置，更新目标值为目标值减去当前数字
        backtrack($candidates, $target - $candidates[$i], $i, $combination, $result);

        // 回溯到上一步，移除当前数字
        array_pop($combination);
    }
}

// 示例用法
$candidates = [2, 3, 6, 7];
$target = 7;
$result = combinationSum($candidates, $target);
print_r($result);


// 当处理实际场景时，回溯算法常常用于解决组合、排列、子集等问题。以下是一个示例场景：假设有一组数字，我们需要找出所有可能的组合使其和等于给定目标值。


// 组合测试
function backtrackDiy1($mubiao, $arrayNum, $foreachKey = -1, $zuhe = [], &$result = [])
{
    $zuheSum = array_sum($zuhe);
    if($zuheSum == $mubiao){
        $result[] = $zuhe;
    }
    if($zuheSum >= $mubiao){
        return;
    }

    foreach($arrayNum as $key => $number)
    {
        if($key < $foreachKey)continue;
        $zuhe[] = $number;
        backtrackDiy1($mubiao, $arrayNum, $key, $zuhe, $result);
        // 回溯树枝
        array_pop($zuhe);
    }

    return $result;
}
$target = 7;
$candidates = [2, 3, 6, 7];
print_r(backtrackDiy1($target, $candidates));
$target = 9;
$candidates = [3, 5, 4, 1, 2, 10];
print_r(backtrackDiy1($target, $candidates));


// 那这个算法 和 枚举算法的 树枝回溯，树枝迭代 看起来是一模一样的。
// 就好像枚举算法就是 回溯算法 的一个针对场景的分支一样
// 我的这个理解是正确的吗？


function backtrackSubsets($nums, $startIndex, $subset, &$result) {
    $result[] = $subset;

    for ($i = $startIndex; $i < count($nums); $i++) {
        $subset[] = $nums[$i];
        backtrackSubsets($nums, $i + 1, $subset, $result);
        array_pop($subset);
    }
}
echo "\r\n--------------------------------------------\r\n";
$nums = [1, 2, 3];
$subset = [];
$result = [];
backtrackSubsets($nums, 0, $subset, $result);
print_r($result);


echo "\r\n--------------------------------------------\r\n";

function backtrackZiJi($array, $zuhe = [], $keyNum = -1, &$result = []) {
    $result[] = $zuhe;
    foreach($array as $key => $value){
        if($key <= $keyNum)continue;
        $zuhe[] = $value;
        backtrackZiJi($array, $zuhe, $key, $result);
        array_pop($zuhe);
    }
    return $result;
}
$array = [1, 2, 3];
print_r(backtrackZiJi($array));

