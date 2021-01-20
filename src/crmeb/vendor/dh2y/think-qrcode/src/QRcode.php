<?php
namespace dh2y\qrcode;



use think\Exception;
use think\facade\Config;
use think\facade\Request;



class QRcode
{


    protected  $config = [];        //相关配置
    protected  $cache_dir = '';    //二维码缓存
    protected  $outfile = '';      //输出二维码文件

    protected $error = '';         //错误信息

    public function __construct(){

        $this->config= Config::get('qrcode.');

        if(isset($this->config['cache_dir'])&&$this->config['cache_dir']!=''){
            $this->cache_dir = $this->config['cache_dir'];
        }else{
            $this->cache_dir = 'uploads/qrcode';
        }


        if (!file_exists($this->cache_dir)){
            mkdir ($this->cache_dir,0775,true);
        }

        require("phpqrcode/qrlib.php");
    }


    /**
     * 生成普通二维码
     * @param string $url  生成url地址
     * @param bool $outfile  生成文件路路径
     * @param int $size
     * @param string $evel
     * @return $this
     */
    public function png($url, $outfile, $size=5, $evel='H'){
        if($outfile == '') $outfile = $this->cache_dir.'/'.time().'.png';
        $this->outfile = $outfile;
        \QRcode::png($url,$outfile,$evel,$size,2);
        return $this;
    }


    /**
     * 显示二维码
     */
    public function show(){
        $url = Request::instance()->domain().'/'.$this->outfile;
        exit('<img src="'.$url.'"/>');
    }

    /**
     * 返回url路径
     * @return string
     */
    public function entry(){
        return Request::instance()->domain().'/'.$this->outfile;
    }

    /**
     * 返回生成二维码的相对路径
     * @param bool $ds
     * @return string
     */
    public function getPath($ds=true){
        if($ds){
            return '/'.$this->outfile;
        }else{
            return $this->outfile;
        }

    }

    /**
     * 销毁内容
     */
    public function destroy(){
        @unlink($this->outfile);
    }


    /**
     * 添加logo到二维码中
     * @param $logo
     * @return bool|mixed
     */
    public function logo($logo){
        if (!isset($logo)||$logo=='') {
            $this->error = 'logo不存在';
            return false;
        }
        $QR = imagecreatefromstring(file_get_contents($this->outfile));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

        $this->outfile = $this->cache_dir.'/'.time().'.png';
        imagepng($QR, $this->outfile);
        imagedestroy($QR);
        return $this;
    }


    /**
     * 添加背景图
     * @param int $x 二维码在背景图X轴未知
     * @param int $y 二维码在背景图Y轴未知
     * @param string $dst_path
     * @return $this
     */
    public function background($x=200,$y=500,$dst_path = ''){

        if($dst_path==''){
            $dst_path = $this->config['background'];
        }
        $src_path = $this->outfile;//覆盖图

        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        $src = imagecreatefromstring(file_get_contents($src_path));

        //获取覆盖图图片的宽高
        list($src_w, $src_h) = getimagesize($src_path);

        //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
        imagecopymerge($dst, $src, $x, $y, 0, 0, $src_w, $src_h, 100);

        $this->outfile = $this->cache_dir.'/'.time().'.png';
        imagepng($dst, $this->outfile);//根据需要生成相应的图片
        imagedestroy($dst);
        return $this;
    }

    public function text($text,$size,$locate =[],$color = '#00000000',$font='simsun.ttc', $offset = 0, $angle = 0) {

        $dst_path = $this->outfile;

        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));

        /* 设置颜色 */
        if (is_string($color) && 0 === strpos($color, '#')) {
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } elseif (!is_array($color)) {
            throw new Exception('错误的颜色值');
        }

        //如果字体不存在 用composer项目自己的字体
        if(!is_file($font)){
            $font =  dirname(__FILE__).'/'.$font;
        }

        //获取文字信息
        $info = imagettfbbox($size, $angle, $font, $text);
        $minx = min($info[0], $info[2], $info[4], $info[6]);
        $maxx = max($info[0], $info[2], $info[4], $info[6]);
        $miny = min($info[1], $info[3], $info[5], $info[7]);
        $maxy = max($info[1], $info[3], $info[5], $info[7]);
        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;

        //背景图信息
        list($dst_w, $dst_h) = getimagesize($dst_path);

        if (is_array($locate)) {
            list($posx, $posy) = $locate;
            $x += ($posx=='center')?(($dst_w - $w) / 2):$posx;
            $y += ($posy=='center')?(($dst_h - $h) / 2):$posy;
        } else {
            throw new Exception('不支持的文字位置类型');
        }

        //字体颜色
        $black = imagecolorallocate($dst,  $color[0], $color[1], $color[2]);

        //加入文字
        imagefttext($dst, $size, $angle, $x, $y, $black,$font, $text);


        //生成图片
        $this->outfile = $this->cache_dir.'/'.time().'.png';
        imagepng($dst, $this->outfile);
        imagedestroy($dst);

        return $this;
    }

}