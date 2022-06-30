<?php
namespace ToolSet\Service\Excel;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
class TasksImport implements ToArray,WithCalculatedFormulas
{

    public function array(array $array)
    {
        return $array;
    }
}