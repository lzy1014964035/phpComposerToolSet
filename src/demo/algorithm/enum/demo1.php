<?php

function enumeratePermutations($numbers, $prefix = []) {
    // 如果所有数字都被使用，输出排列结果
    if (count($prefix) === count($numbers)) {
        echo implode(' ', $prefix) . "\n";
        return;
    }

    // 枚举可用的数字
    foreach ($numbers as $number) {
        // 如果数字已经在当前排列中使用过，跳过该数字
        if (in_array($number, $prefix)) {
            continue;
        }

        // 将当前数字添加到排列中
        $prefix[] = $number;

        // 递归调用，继续生成下一个位置的数字
        enumeratePermutations($numbers, $prefix);

        // 回溯，将当前数字从排列中移除，以便尝试其他数字
        $beforePrefix = $prefix;
        array_pop($prefix);
//        print_r(['before' => $beforePrefix, 'after' => $prefix]);
//        die;
    }
}

//// 测试示例
//$numbers = [1, 2, 3, 4];
//enumeratePermutations($numbers);


function enumDeal($array, $zuhe = [], &$result = [])
{
    if(count($array) == count($zuhe)){
        $result[] = $zuhe;
    }
    foreach($array as $value){
        if(in_array($value, $zuhe)){
           continue;
        }
        $zuhe[] = $value;
        enumDeal($array, $zuhe, $result);
        // 回溯当前操作值，之后创造出新的组合（！！！重中之重）
        // 假设当前注入的值2，前面已经注入了1 那么zuhe的内容就是 1，2
        // 然后 进行回溯，将值改变为 1  并且因为当前的value是2，所以在下一轮中，$zuhe就会变成 1，3 并且再次注入到 enumDeal中。
        // 之后下一轮中，因为 1，3都存在与$zuhe中，所以被插入的就是2.这时组合变成 1，3，2.
        // 然后回溯成 1，3并且因为这时value是2，那么下一轮判断的value就是3，但是3存在了，就只能再下一轮，就是$zuhe = 1，3，4 再将$zuhe注入enumDeal，$zuhe在下一个栈中就变成了1，3，4，2。

        // 总结就是，按个节点都进行回溯，并且跳过当前值用下一个值注入到$zuhe时。都相当于创造了一个新的树枝。
        // 再将新的树枝塞进下轮方法中，创造新一轮的树枝，这样就能完成所有可能的获取。

        // 回溯当前操作值，之后创造出新的组合（！！！重中之重）
        // 这段注释的解释是正确的。回溯操作的关键是通过删除最后一个元素（使用array_pop函数）来还原状态，从而在下一轮循环中创造新的组合。
        // 这是回溯算法的核心思想之一，它确保在每个递归层次上都能尝试所有可能的组合。
        array_pop($zuhe);
    }
    return $result;
}

// 根据key进行枚举
function enumDealByKey($array)
{
    $keyArray = array_keys($array);
    $enumValue = enumDeal($keyArray);
    foreach($enumValue as &$valueArray)
    {
        foreach($valueArray as $key => $value)
        {
            $valueArray[$key] = $array[$value];
        }
    }
    return $enumValue;
}


$result = enumDeal([1, 2, 3, 4]);
$result2 = enumDealByKey(['a', 'a', 'b', 'c']);

print_r([$result, $result2]);