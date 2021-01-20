<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\services;


class SpreadsheetExcelService
{
    //
    private static $instance = null;
    //PHPSpreadsheet实例化对象
    private static $spreadsheet = null;
    //sheet实例化对象
    private static $sheet = null;
    //表头计数
    protected static $count;
    //表头占行数
    protected static $topNumber = 3;
    //表能占据表行的字母对应self::$cellkey
    protected static $cells;
    //表头数据
    protected static $data = [];
    //文件名
    protected static $title = '订单导出';
    //行宽
    protected static $width = 20;
    //行高
    protected static $height = 50;
    //保存文件目录
    protected static $path = './phpExcel/';
    //设置style
    private static $styleArray = [
//         'borders' => [
//             'allBorders' => [
// //                PHPExcel_Style_Border里面有很多属性，想要其他的自己去看
//                // 'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,//边框是粗的
// //                'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,//双重的
// //                'style' => \PHPExcel_Style_Border::BORDER_HAIR,//虚线
// //                'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,//实粗线
// //                'style' => \PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT,//虚粗线
// //                'style' => \PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT,//点虚粗线
//                 'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,//细边框
//                 // 'color' => ['argb' => 'FFFF0000'],
//             ],
//         ],
        'font' => [
            'bold' => true
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
    ];

    private function __construct(){}

    private function __clone(){}

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$spreadsheet = $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            self::$sheet = $spreadsheet->getActiveSheet();
        }
        return self::$instance;
    }

    /**
     *设置字体格式
     * @param $title string 必选
     * return string
     */
    public static function setUtf8($title)
    {
        return iconv('utf-8', 'gb2312', $title);
    }
    /**
     *  创建保存excel目录
     *  return string
     */
    public static function savePath()
    {
        if(!is_dir(self::$path)){
            if (mkdir(self::$path, 0700) == false) {
                return false;
            }
        }
        //年月一级目录
        $mont_path = self::$path.date('Ym');
        if(!is_dir($mont_path)){
            if (mkdir($mont_path, 0700) == false) {
                return false;
            }
        }
        //日二级目录
        $day_path = $mont_path.'/'.date('d');
        if(!is_dir($day_path)){
            if (mkdir($day_path, 0700) == false) {
                return false;
            }
        }
        return $day_path;
    }
    /**
     * 设置标题
     * @param $title string || array ['title'=>'','name'=>'','info'=>[]]
     * @param $Name string
     * @param $info string || array;
     * @param $funName function($style,$A,$A2) 自定义设置头部样式
     * @return $this
     */
    public function setExcelTile($title = '', $Name = '', $info = [], $funName = null)
    {
        //设置参数
        if (is_array($title)) {
            if (isset($title['title'])) $title = $title['title'];
            if (isset($title['name'])) $Name = $title['name'];
            if (isset($title['info'])) $info = $title['info'];
        }
        if (empty($title))
            $title = self::$title;
        else
            self::$title = $title;

        if (empty($Name)) $Name = time();
        //设置Excel属性
        self::$spreadsheet->getProperties()
            ->setCreator("Neo")
            ->setLastModifiedBy("Neo")
            ->setTitle(self::setUtf8($title))
            ->setSubject($Name)
            ->setDescription("")
            ->setKeywords($Name)
            ->setCategory("");
        self::$sheet->setTitle($Name);
        self::$sheet->setCellValue('A1', $title);
        self::$sheet->setCellValue('A2', self::setCellInfo($info));
        //文字居中
        self::$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        self::$sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        //合并表头单元格
        self::$sheet->mergeCells('A1:' . self::$cells . '1');
        self::$sheet->mergeCells('A2:' . self::$cells . '2');

        self::$sheet->getRowDimension(1)->setRowHeight(40);
        self::$sheet->getRowDimension(2)->setRowHeight(20);

        //设置表头字体
        self::$sheet->getStyle('A1')->getFont()->setName('黑体');
        self::$sheet->getStyle('A1')->getFont()->setSize(20);
        self::$sheet->getStyle('A1')->getFont()->setBold(true);
        self::$sheet->getStyle('A2')->getFont()->setName('宋体');
        self::$sheet->getStyle('A2')->getFont()->setSize(14);
        self::$sheet->getStyle('A2')->getFont()->setBold(true);

        self::$sheet->getStyle('A3:' . self::$cells . '3')->getFont()->setBold(true);
        return $this;
    }

    /**
     * 设置第二行标题内容
     * @param $info  array (['name'=>'','site'=>'','phone'=>123] || ['我是表名','我是地址','我是手机号码'] ) || string 自定义
     * @return string
     */
    private static function setCellInfo($info)
    {
        $content = ['操作者：', '导出日期：' . date('Y-m-d', time()), '地址：', '电话：'];
        if (is_array($info) && !empty($info)) {
            if (isset($info['name'])) {
                $content[0] .= $info['name'];
            } else {
                $content[0] .= isset($info[0]) ? $info[0] : '';
            }
            if (isset($info['site'])) {
                $content[2] .= $info['site'];
            } else {
                $content[2] .= isset($info[1]) ? $info[1] : '';
            }
            if (isset($info['phone'])) {
                $content[3] .= $info['phone'];
            } else {
                $content[3] .= isset($info[2]) ? $info[2] : '';
            }
            return implode(' ', $content);
        } else if (is_string($info)) {
            return empty($info) ? implode(' ', $content) : $info;
        }
    }
    /**
     * 设置头部信息
     * @param $data array
     * @return $this
     */
    public static function setExcelHeader($data)
    {
        $span = 'A';
        foreach ($data as $key => $value) {
            self::$sheet->getColumnDimension($span)->setWidth(self::$width);
            self::$sheet->setCellValue($span.self::$topNumber, $value);
            $span++;
        }
        self::$sheet->getRowDimension(3)->setRowHeight(self::$height);
        self::$cells = $span;
        return new self;
    }
    /**
     *
     * execl数据导出
     * @param  $data 需要导出的数据 格式和以前的可是一样
     *
     * 特殊处理：合并单元格需要先对数据进行处理
     */
    public function setExcelContent($data = [])
    {
        if (!empty($data) && is_array($data)) {
            $column = self::$topNumber+1;
            // 行写入
            foreach ($data as $key => $rows) {
                $span = 'A';
                // 列写入
                foreach ($rows as $keyName => $value) {
                    self::$sheet->setCellValue($span . $column, $value);
                    $span++;
                }
                $column++;
            }
            self::$sheet->getDefaultRowDimension()->setRowHeight(self::$height);
            //设置内容字体样式
            self::$sheet->getStyle('A1:' . $span.$column)->applyFromArray(self::$styleArray);
            //设置边框
            self::$sheet->getStyle('A1:' . $span.$column)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            //设置自动换行
            self::$sheet->getStyle('A4:' . $span.$column)->getAlignment()->setWrapText(true);
        }
        return new self;
    }

    /**
     * 保存表格数据，直接下载
     * @param $filename 文件名称
     * @param $suffix 文件后缀名
     * @param $is_save 是否保存文件
     * @return 保存文件：return string
     */
    public function excelSave($fileName = '',$suffix = 'xlsx',$is_save = false)
    {
        if(empty($fileName)){
            $fileName = date('YmdHis').time();
        }
        if(empty($suffix)){
            $suffix = 'xlsx';
        }
        // 重命名表（UTF8编码不需要这一步）
        if (mb_detect_encoding($fileName) != "UTF-8") {
            $fileName = iconv("utf-8", "gbk//IGNORE", $fileName);
        }
        if ($suffix == 'xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $class = "\PhpOffice\PhpSpreadsheet\Writer\Xlsx";
        } elseif ($suffix == 'xls') {
            header('Content-Type:application/vnd.ms-excel');
            $class = "\PhpOffice\PhpSpreadsheet\Writer\Xls";
        }
        // 清理缓存
//        ob_end_clean();
        $spreadsheet = self::$spreadsheet;
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        if(!$is_save){//直接下载

            header('Content-Disposition: attachment;filename="' . $fileName . '.' . $suffix . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            // 删除清空 释放内存
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            exit;
        }else{//保存文件
            $path = self::savePath().'/'.$fileName.'.'.$suffix;
            //$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            //$writer->save($path);
            $writer->save($path);
            // 删除清空 释放内存
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            return $path;
        }
    }

}
