<?php
namespace lzy1014964035\PHPComposerToolSet\Service\Excel;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
class TasksImport implements ToArray,WithCalculatedFormulas
{

    public function array(array $array)
    {
        return $array;
    }
}