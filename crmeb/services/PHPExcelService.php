<?php
namespace crmeb\services;

use crmeb\services\JsonService as Json;

class PHPExcelService
{
    //PHPExcel实例化对象
    private static $PHPExcel=null;
    //表头计数
    protected static $count;
    //表头占行数
    protected static $topNumber = 3;
    //表能占据表行的字母对应self::$cellkey
    protected static $cells;
    //表头数据
    protected static $data=[];
    //文件名
    protected static $title='订单导出';
    //行宽
    protected static $where=20;
    //行高
    protected static $height=50;
    //表行名
    private static $cellKey = array(
        'A','B','C','D','E','F','G','H','I','J','K','L','M',
        'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
        'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
    );
    //设置style
    private static $styleArray = array(
        'borders' => array(
            'allborders' => array(
//                PHPExcel_Style_Border里面有很多属性，想要其他的自己去看
//                'style' => \PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
//                'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,//双重的
//                'style' => \PHPExcel_Style_Border::BORDER_HAIR,//虚线
//                'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,//实粗线
//                'style' => \PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT,//虚粗线
//                'style' => \PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT,//点虚粗线
                'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框
                //'color' => array('argb' => 'FFFF0000'),
            ),
        ),
        'font'=>[
            'bold'=>true
        ],
        'alignment'=>[
            'horizontal'=>\PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER
        ]
    );
    /**
     *初始化PHPExcel类
     *@param $data array()
     *@param $fun function()
     * return
     */
    private static function initialize($data,$fun){
        self::$PHPExcel= new \PHPExcel();
        if($fun!==null && is_callable($fun)){
            self::$styleArray=$fun();
        }
        if(!is_array($data)) exit(Json::fail('data 为数组'));
        self::$data=$data;
    }
    /**
     *设置字体格式
     *@param $title string 必选
     * return string
     */
    public static function setUtf8($title){
        return iconv('utf-8', 'gb2312', $title);
    }
    /**
     *
     * execl数据导出
     * @param  $list 需要导出的数据 格式和以前的可是一样
     * @param  $list 也可以为匿名函数 匿名函数参数有 $sheet PHPExcel->getActiveSheet(),self::$topNumber 从第几行设置,$cellkey 行号为数组,self::$cells现在设置的最大行号
     *
     * 特殊处理：合并单元格需要先对数据进行处理
     */
    public function setExcelContent($list=null)
    {
        $sheet=self::$PHPExcel->getActiveSheet();
        foreach(self::$data as $key=>$val){
            $row=self::$cellKey[$key];
            $sheet->getColumnDimension($row)->setWidth(isset($val['w'])?$val['w']:self::$where);
            $sheet->setCellValue($row.self::$topNumber,isset($val['name'])?$val['name']:$val);
        }
        $cellkey=array_slice(self::$cellKey,0,self::$count);
        if($list!==null && is_array($list)){
            foreach ($cellkey as $k=>$v){
                foreach ($list as $key=>$val){
                    if(isset($val[$k]) && !is_array($val[$k])){
                        $sheet->setCellValue($v.(self::$topNumber+1+$key),$val[$k]);
                    }else if(isset($val[$k]) && is_array($val[$k])){
                        $str='';
                        foreach ($val[$k] as $value){
                            $str.=$value.chr(10);
                        }
                        $sheet->setCellValue($v.(self::$topNumber+1+$key),$str);
                    }
                }
            }
            $sheet->getDefaultRowDimension()->setRowHeight(self::$height);
            //设置边框
            $sheet->getStyle('A1:'.self::$cells.(count($list)+self::$topNumber))->applyFromArray(self::$styleArray);
            //设置自动换行
            $sheet->getStyle('A4:'.self::$cells.(count($list)+self::$topNumber))->getAlignment()->setWrapText(true);
        }else if($list!==null && is_callable($list)){
            $list($sheet,self::$topNumber,$cellkey,self::$cells)->applyFromArray(self::$styleArray);
        }

        return $this;
    }
    /**
     * 保存表格数据，并下载
     * @param
     * @return
     */
    public function ExcelSave(){
        $objWriter=\PHPExcel_IOFactory::createWriter(self::$PHPExcel,'Excel2007');
        $filename=self::$title.'--'.time().'.xlsx';
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 设置头部信息
     * @param $data array
     * @param $fun function() 主要设置边框的粗细
     * @return $this
     */
    public static function setExcelHeader($data,$fun=null)
    {
        self::initialize($data,$fun);

        if(self::$count=count(self::$data)){
            if(self::$count>count(self::$cellKey)){
                return Json::fail('表头长度过长');
            }
            self::$cells=self::$cellKey[self::$count-1];
        }else{
            return Json::fail('data 参数二不能为空');
        }
        return new self;
    }
    /**
     * 设置标题
     * @param $title string || array ['title'=>'','name'=>'','info'=>[]]
     * @param $Name string
     * @param $info string || array;
     * @param $funName function($style,$A,$A2) 自定义设置头部样式
     * @return $this
     */
    public function setExcelTile($title='',$Name='',$info=[],$funName=null){
        //设置参数
        if(is_array($title)){
            if(isset($title['title'])) $title=$title['title'];
            if(isset($title['name'])) $Name=$title['name'];
            if(isset($title['info'])) $info=$title['info'];
        }
        if(empty($title))
            $title=self::$title;
        else
            self::$title=$title;
        if(empty($Name)) $Name=time();
        //设置Excel属性
        self::$PHPExcel ->getProperties()
            ->setCreator("Neo")
            ->setLastModifiedBy("Neo")
            ->setTitle(self::setUtf8($title))
            ->setSubject($Name)
            ->setDescription("")
            ->setKeywords($Name)
            ->setCategory("");
        self::$PHPExcel ->getActiveSheet()->setCellValue('A1', $title);
        //文字居中
        self::$PHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        self::$PHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        self::$PHPExcel->setActiveSheetIndex(0);
        self::$PHPExcel->getActiveSheet()->setTitle($Name);

        self::$PHPExcel->getActiveSheet()->setCellValue('A2',self::setCellInfo($info));
        //合并表头单元格
        self::$PHPExcel->getActiveSheet()->mergeCells('A1:'.self::$cells.'1');
        self::$PHPExcel->getActiveSheet()->mergeCells('A2:'.self::$cells.'2');

        self::$PHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
        self::$PHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
        //设置表头行高
        if($funName!==null && is_callable($funName)){
            $fontstyle=self::$PHPExcel->getActiveSheet();
            $funName($fontstyle,'A1','A2');
        }else{
            //设置表头字体
            self::$PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('黑体');
            self::$PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
            self::$PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            self::$PHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体');
            self::$PHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(14);
        }
        self::$PHPExcel->getActiveSheet()->getStyle('A3:'.self::$cells.'3')->getFont()->setBold(true);

        return $this;
    }
    /**
     * 设置第二行标题内容
     * @param $info  array (['name'=>'','site'=>'','phone'=>123] || ['我是表名','我是地址','我是手机号码'] ) || string 自定义
     * @return string
     */
    private static function setCellInfo($info){
        $content=['操作者：','导出日期：'.date('Y-m-d',time()),'地址：','电话：'];
        if(is_array($info) && !empty($info)){
            if(isset($info['name'])){
                $content[0].=$info['name'];
            }else{
                $content[0].=isset($info[0])?$info[0]:'';
            }
            if(isset($info['site'])){
                $content[2].=$info['site'];
            }else{
                $content[2].=isset($info[1])?$info[1]:'';
            }
            if(isset($info['phone'])){
                $content[3].=$info['phone'];
            }else{
                $content[3].=isset($info[2])?$info[2]:'';
            }
            return implode(' ',$content);
        }else if(is_string($info)){
            return empty($info)?implode(' ',$content):$info;
        }
    }
}
