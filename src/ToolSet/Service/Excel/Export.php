<?php

namespace ToolSet\Service\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Border;



/**excel导出需要用的内容对象类
 * 放到其他的位置不是很合适，所以就放在这里了
 * Class Export
 * @package App\Service
 */
class Export implements FromCollection, WithHeadings, WithEvents
{
    protected $data;
    protected $headings;
    protected $columnWidth = [];//设置列宽       key：列  value:宽
    protected $rowHeight = [];  //设置行高       key：行  value:高
    protected $mergeCells = []; //合并单元格    value:A1:K8
    protected $font = [];       //设置字体       key：A1:K8  value:Arial
    protected $fontSize = [];       //设置字体大小       key：A1:K8  value:11
    protected $bold = [];       //设置粗体       key：A1:K8  value:true
    protected $background = []; //设置背景颜色    key：A1:K8  value:#F0F0F0F
    protected $vertical = [];   //设置定位       key：A1:K8  value:center
    protected $sheetName; //sheet title
    protected $borders = []; //设置边框颜色  key：A1:K8  value:#000000
    protected $selectMenu = []; // 下拉选框组
    protected $dateArray = []; // 日期个数组
    //设置页面属性时如果无效   更改excel格式尝试即可
    //构造函数传值
    public function __construct($data, $headings, $sheetName = "")
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->sheetName = $sheetName;
        $this->createData();
    }

    public function headings(): array
    {
        return $this->headings;
    }

    //数组转集合
    public function collection()
    {
        return new Collection($this->data);
    }

    //业务代码
    public function createData()
    {
        $this->data = collect($this->data)->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //设置区域单元格垂直居中
                $event->sheet->getDelegate()->getStyle('A1:Z1265')->getAlignment()->setVertical('center');
                //设置区域单元格水平居中
                $event->sheet->getDelegate()->getStyle('A1:Z1265')->getAlignment()->setHorizontal('center');

                //设置列宽
                foreach ($this->columnWidth as $column => $width) {
                    $event->sheet->getDelegate()
                        ->getColumnDimension($column)
                        ->setWidth($width);
                }
                //设置行高，$i为数据行数
                foreach ($this->rowHeight as $row => $height) {
                    $event->sheet->getDelegate()
                        ->getRowDimension($row)
                        ->setRowHeight($height);
                }

                //设置区域单元格垂直居中
                foreach ($this->vertical as $region => $position) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getAlignment()
                        ->setVertical($position);
                }

                //设置区域单元格字体
                foreach ($this->font as $region => $value) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()->setName($value);
                }
                //设置区域单元格字体大小
                foreach ($this->fontSize as $region => $value) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()
                        ->setSize($value);
                }

                //设置区域单元格字体粗体
                foreach ($this->bold as $region => $bool) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()
                        ->setBold($bool);
                }


                //设置区域单元格背景颜色
                foreach ($this->background as $region => $item) {
                    $event->sheet->getDelegate()->getStyle($region)->applyFromArray([
                        'fill' => [
                            'fillType' => 'linear', //线性填充，类似渐变
                            'startColor' => [
                                'rgb' => $item //初始颜色
                            ],
                            //结束颜色，如果需要单一背景色，请和初始颜色保持一致
                            'endColor' => [
                                'argb' => $item
                            ]
                        ]
                    ]);
                }
                //设置边框颜色
                foreach ($this->borders as $region => $item) {
                    $event->sheet->getDelegate()->getStyle($region)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => $item],
                            ],
                        ],
                    ]);
                }
                //合并单元格
                $event->sheet->getDelegate()->setMergeCells($this->mergeCells);
                if (!empty($this->sheetName)) {
                    $event->sheet->getDelegate()->setTitle($this->sheetName);
                }


                // 下拉选框
                foreach($this->selectMenu as $column => $selectArray)
                {
                    for ($i = 2; $i <= 100; $i++) {
                        $validation = $event->sheet->getDelegate()->getCell($column . $i)->getDataValidation();
                        $this->setSelectMenu($validation, $selectArray);
                    }
                }

                // 日期
                foreach($this->dateArray as $column)
                {
                    for ($i = 2; $i <= 100; $i++) {
                        $validation = $event->sheet->getDelegate()->getCell($column . $i)->getDataValidation();
                        $this->setDate($validation);
                    }
                }

            }
        ];
    }

    /**
     * @return array
     * [
     *    'B' => 40,
     *    'C' => 60
     * ]
     */
    public function setColumnWidth(array $columnwidth)
    {
        $this->columnWidth = array_change_key_case($columnwidth, CASE_UPPER);
    }

    /**
     * @return array
     * [
     *    1 => 40,
     *    2 => 60
     * ]
     */
    public function setRowHeight(array $rowHeight)
    {
        $this->rowHeight = $rowHeight;
    }

    /**
     * @return array
     * [
     *    A1:K7 => '宋体'
     * ]
     */
    public function setFont(array $font)
    {
        $this->font = array_change_key_case($font, CASE_UPPER);
    }

    /**
     * @return array
     * @2020/3/22 10:33
     * [
     *    A1:K7 => true
     * ]
     */
    public function setBold(array $bold)
    {
        $this->bold = array_change_key_case($bold, CASE_UPPER);
    }

    /**
     * @return array
     * @2020/3/22 10:33
     * [
     *    A1:K7 => F0FF0F
     * ]
     */
    public function setBackground(array $background)
    {
        $this->background = array_change_key_case($background, CASE_UPPER);
    }

    /**
     * @return array
     * [
     *    A1:K7
     * ]
     */
    public function setMergeCells(array $mergeCells)
    {
        $this->mergeCells = array_change_key_case($mergeCells, CASE_UPPER);
    }

    /**
     * @return array
     * [
     *    A1:K7 => 14
     * ]
     */
    public function setFontSize(array $fontSize)
    {
        $this->fontSize = array_change_key_case($fontSize, CASE_UPPER);
    }

    /**
     * @return array
     * [
     *    A1:K7 => #000000
     * ]
     */
    public function setBorders(array $borders)
    {
        $this->borders = array_change_key_case($borders, CASE_UPPER);
    }

    /**
     * 根据key值获取对应的列标名称
     * @param $key
     * @return mixed|string
     */
    public function getKeyName($key)
    {
        $keyArray = [
            "A", "B", "C", "D", "E",
            "F", "G", "H", "I", "J",
            "K", "L", "M", "N", "O",
            "P", "Q", "R", "S", "T",
            "U", "V", "W", "X", "Y", "Z"
        ];

        // 最多计算到ZZ 再往后的就不计算了
        $keyName = $key;
        if ($key < 26) {
            $keyName = $keyArray[$key];
        } elseif ($key < 702) {
            // 十位数向下取整得出
            $tenFigures = floor($key / 26);
            // 个位数取余数得下标
            $singleDigit = $key % 26;

            $keyName = $keyArray[$tenFigures - 1] . $keyArray[$singleDigit];
        }

        return $keyName;
    }

    /**
     * 给导出的内容自动设置宽度
     * @param $header
     * @param $data
     * @return array
     */
    public function setColumnAutoWidth()
    {
        $header = $this->headings;
        $data = $this->data;

        // 把header也放进去做长度检测
        $data[] = $header;
        $strLenArray = [];
        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                if (!isset($strLenArray[$key])) {
                    $strLenArray[$key] = 0;
                }
                $len = strlen($value);

                if ($len > $strLenArray[$key]) {
                    $strLenArray[$key] = $len;
                }
            }
        }

        $returnData = [];

        foreach ($strLenArray as $key => $value) {
            $keyName = $this->getKeyName($key);
            $returnData[$keyName] = $value + 2;
        }

        $this->setColumnWidth($returnData);
    }

    /**
     * 设置下拉选框
     * @param DataValidation $validation
     * @param array $selects
     * @return DataValidation
     */
    protected function setSelectMenu(DataValidation $validation, array $selects = [])
    {
        $args = '"' . implode(",", $selects) . '"';
//        dd($selects, strlen($args));
        return $validation->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle("输入的值有误")
            ->setError("您输入的值不在下拉框列表内.")
            ->setPromptTitle('从选项中选择')
            ->setPrompt('请从下拉列表中选择一个值。')
            ->setFormula1($args);
    }

    /**
     * 设置为日期
     * @param DataValidation $validation
     * @return DataValidation
     */
    protected function setDate(DataValidation $validation)
    {
        return $validation->setType(DataValidation::TYPE_DATE)->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle("输入的值有错误")
            ->setError("请输入有效日期格式.")
            ->setPromptTitle('请输入日期')
            ->setPrompt('该位置需要输入正确的日期格式，如2020/5/2 或 2020-05-02');
    }


    /**
     * 制作模板
     * @param $configData
     * @return Export
     */
    public static function makeTemplate($configData)
    {
        $excel = new Export([], []);
        $excel->setTemplateConfig($configData);
        $excel->setColumnAutoWidth();
        return $excel;
    }

    /**
     * 设置模板配置
     * @param $configData
     */
    public function setTemplateConfig($configData)
    {
        foreach($configData as $key => $data)
        {
            // 名称
            $this->headings[] = $data['name'];

            // 下拉选框配置
            if(isset($data['type']) && $data['type'] == "selectList"){
                $keyName = $this->getKeyName($key);
                $selectList = $data['selectList'];
                $this->selectMenu[$keyName] = $selectList;
            }

            // 下拉选框配置
            if(isset($data['type']) && $data['type'] == "date"){
                $keyName = $this->getKeyName($key);
                $this->dateArray[] = $keyName;
            }
        }
    }
}

