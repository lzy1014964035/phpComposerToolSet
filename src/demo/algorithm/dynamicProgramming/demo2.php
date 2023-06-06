<?php



function makeNumArray($arrayCount)
{
    $result = [];
    while(true){
        $num = mt_rand(0, $arrayCount * 5);
        if( ! in_array($num, $result)){
            $result[] = $num;
        }
        if(count($result) >= $arrayCount){
            break;
        }
    }
    return $result;
}

$data = makeNumArray(100);
echo json_encode($data);
$array = [1,183,2,4,3,5,426,307,283,287,448,164,478,204,37,197,476,141,255,285,358,144,33,264,410,76,383,422,322,90,232,417,191,398,69,356,373,452,372,189,482,267,489,135,332,99,436,214,430,273,253,406,323,212,288,112,449,146,194,104,158,437,72,499,59,175,495,193,162,85,292,341,488,300,336,42,48,157,347,445,263,440,439,98,408,450,19,306,71,199,167,305,366,38,473,60,350,312,389,270];

// 找出最长子序列
function returnMaxTreeChild($array, $startKey = 0, $lastValue = 0, &$inTreeNode = [])
{
    $count = count($array);
    for($key = $startKey; $key < $count; $key++)
    {
        $value = $array[$key];
        if(isset($inTreeNode[$value])){
            continue;
        }
        if($value == $lastValue * 2 || $value == $lastValue + 1){
            $inTreeNode[$value] = array_merge($inTreeNode[$lastValue] ?? [], [$value]);
            returnMaxTreeChild($array, $key, $value, $inTreeNode);
        }
    }
    return $inTreeNode;
}

var_dump(returnMaxTreeChild($array));die;
