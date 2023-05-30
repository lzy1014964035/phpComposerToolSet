<?php

function backtrack($nums, $start, &$subset, &$result) {
    // 将当前子集加入结果集
    $result[] = $subset;

    // 从当前位置开始回溯
    for ($i = $start; $i < count($nums); $i++) {
        // 将当前元素加入子集
        $subset[] = $nums[$i];

        // 递归回溯，从下一个位置开始
        backtrack($nums, $i + 1, $subset, $result);

        // 回溯到上一步，移除当前元素
        array_pop($subset);
    }
}

function subsets($nums) {
    $result = [];
    $subset = [];
    backtrack($nums, 0, $subset, $result);
    return $result;
}

// 示例用法
$nums = [1, 2, 3];
$result = subsets($nums);
print_r($result);

// 在上述示例中，回溯算法通过递归调用backtrack函数来搜索所有可能的子集。在每一步中，我们将当前元素加入子集，并递归调用backtrack函数来继续搜索下一个位置。当搜索到最后一个位置时，将当前子集加入结果集。然后，进行回溯到上一步，移除当前元素，以便尝试其他可能的选择。
//
//通过不断的回溯和选择，最终得到了所有可能的子集。
//
//需要注意的是，回溯算法通常涉及递归调用和状态的回退，因此在实际使用中需要注意控制递归深度和合理管理状态。同时，对于大规模问题，回溯算法可能会导致指数级的计算复杂度，因此需要谨慎使用，并结合剪枝策略进行优化。