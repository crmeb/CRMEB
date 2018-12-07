<?php
namespace  app\routine\model\routine;


class RoutineCode{

    /**
     * 获取分销二维码
     * @param int $uid  yonghuID
     * @param array $color 二维码线条颜色
     * @return mixed
     */
    public static function getCode($uid = 0,$imgUrl = '',$color = array(),$page = '',$thirdType = 'spread'){
        $accessToken = RoutineServer::get_access_token();
        $res = RoutineQrcode::setRoutineQrcodeForever($uid,$thirdType,$page,$imgUrl);
        if($res){
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$accessToken;
            if($uid) $data['scene'] = $res->id;
            else $data['scene'] = 0;
            if(empty($color)){
                $color['r'] = 0;
                $color['g'] = 0;
                $color['b'] = 0;
            }
            $data['page'] = '';
            $data['width'] = 430;
            $data['auto_color'] = false;
            $data['line_color'] = $color;
            $data['is_hyaline'] = false;
            $resCode = RoutineServer::curlPost($url,json_encode($data));
            if($resCode){
                $dataQrcode['status'] = 1;
                $dataQrcode['url_time'] = time();
                $res = RoutineQrcode::setRoutineQrcodeFind($res->id,$dataQrcode);
                if($res) return $resCode;
                else return false;
            }else return false;
        }else return false;
    }

    /**
     * 获取小程序内访问页面的二维码
     * @param string $path
     * @param int $width
     * @return mixed
     */
    public static function getPages($path = '',$width = 430){
        $accessToken = RoutineServer::get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$accessToken;
        $data['path'] = $path;
        $data['width'] = $width;
        return RoutineServer::curlPost($url,json_encode($data));
    }
}