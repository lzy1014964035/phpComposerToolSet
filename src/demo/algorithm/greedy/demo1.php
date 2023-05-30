<?php
// 
class CityPathFindService
{
    public function calculateDistance($cities, $path) {
        $distance = 0;
        $numCities = count($cities);

        for ($i = 0; $i < $numCities - 1; $i++) {
            $currentCity = $cities[$path[$i]];
            $nextCity = $cities[$path[$i + 1]];

            // 计算当前城市和下一个城市之间的距离
            $distance += $this->calculateDistanceBetweenCities($currentCity, $nextCity);
        }

        // 返回总距离以及回到起点的距离
        $distance += $this->calculateDistanceBetweenCities($cities[$path[$numCities - 1]], $cities[$path[0]]);

        return $distance;
    }

    public function findShortestPath($cities) {
        $numCities = count($cities);
        $shortestPath = range(0, $numCities - 1); // 初始化为默认顺序路径
        $shortestDistance = $this->calculateDistance($cities, $shortestPath);

        // 生成所有可能的路径
        $permutations = $this->permute(range(0, $numCities - 1));

        // 遍历所有路径，计算每个路径的总距离并更新最短路径和距离
        foreach ($permutations as $path) {
            $distance = $this->calculateDistance($cities, $path);
            if ($distance < $shortestDistance) {
                $shortestPath = $path;
                $shortestDistance = $distance;
            }
        }

        return $shortestPath;
    }

    // 计算两个城市之间的距离（示例函数）
    public function calculateDistanceBetweenCities($city1, $city2) {
        // 实际情况中，你需要根据具体的城市坐标或其他方式来计算距离
        // 这里只是一个示例，假设城市之间的距离是已知的
        $distances = [
            [0, 10, 15, 20],
            [10, 0, 35, 25],
            [15, 35, 0, 30],
            [20, 25, 30, 0],
        ];

        return $distances[$city1][$city2];
    }

    // 生成所有可能的排列组合（全排列函数）
    public function permute($elements) {
        if (count($elements) <= 1) {
            return [$elements];
        }

        $permutations = [];
        foreach ($elements as $index => $element) {
            $rest = $elements;
            unset($rest[$index]);

            foreach ($this->permute($rest) as $permutation) {
                $permutations[] = array_merge([$element], $permutation);
            }
        }

        return $permutations;
    }

}

// 示例使用
$cities = ['A', 'B', 'C', 'D'];
$service = new CityPathFindService();
$shortestPath = $service->findShortestPath($cities);
$shortestDistance = $service->calculateDistance($cities, $shortestPath);

echo "最短路径：";

echo "最短距离：$shortestDistance";