<?php

namespace app\admin\controller\wechat;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
use app\admin\model\wechat\WechatReply;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use crmeb\services\UploadService as Upload;

/**
 * 关键字管理  控制器
 * Class Reply
 * @package app\admin\controller\wechat
 */
class Reply extends AuthController
{
    /**关注回复
     * @return mixed|void
     */
    public function index()
    {
        if(empty(input('key'))) return $this->failed('请输入参数key');
        if(empty(input('title'))) return $this->failed('请输入参数title');
        $key = input('key');
        switch($key){
            case 'subscribe':
                $title = '编辑关注回复';
                break;
            case 'default':
                $title = '编辑关键字默认回复';
                break;
            default:
                $title = '编辑关键字回复';
                break;
        }
        $this->assign('replay_arr',WechatReply::getDataByKey($key));
        $this->assign('key',$key);
        $this->assign('title',$title);
        return $this->fetch();
    }

    public function one_reply(){
        $where = Util::postMore([
            ['key'],
            ['add',0],
        ],$this->request);
        if(!empty($where['key'])) $replay_arr = WechatReply::getDataByKey($where['key']);
        $replay_arr['code'] = 200;
        if(empty($replay_arr)) {
            $replay_arr['code'] = 0;
        }
        if($where['add'] && empty($where['key'])){
            $replay_arr['code'] = 0;
        }
        exit(json_encode($replay_arr));
    }

    public function save()
    {
        $data = Util::postMore([
            'type',
            'key',
            ['status',0],
            ['data',[]],
        ]);
        if(!isset($data['type']) && empty($data['type']))
            return Json::fail('请选择回复类型');
        if(!in_array($data['type'],WechatReply::$reply_type))
            return Json::fail('回复类型有误!');
        if(!isset($data['data']) || !is_array($data['data']))
            return Json::fail('回复消息参数有误!');
        $res = WechatReply::redact($data['data'],$data['key'],$data['type'],$data['status']);
        if(!$res)
            return Json::fail(WechatReply::getErrorInfo());
        else
            return Json::successful('保存成功!',$data);
    }

    public function upload_img()
    {
        $name = $this->request->post('file');
        if(!$name) return Json::fail('请上传图片');
        $res = Upload::image($name,'wechat/image',true,true,null,'uniqid',1);
        if(!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],0,$res['image_type'],$res['time']);
        return Json::successful('上传成功',$res['dir']);
    }

    public function upload_file()
    {
        $name = $this->request->post('file');
        if(!$name) return Json::fail('请上传声音');
        $autoValidate['size'] = 2097152;
        $res = Upload::file($name,'wechat/voice',true,$autoValidate);
        return $res->status === true ? Json::successful('上传成功',$res->filePath) : Json::fail($res->error);
    }

    /**
     * 关键字回复
     * */
    public function keyword(){
        $where = Util::getMore([
            ['key',''],
            ['type',''],
        ]);
        $this->assign('where',$where);
        $this->assign(WechatReply::getKeyAll($where));
        return $this->fetch();

    }
    /**
     * 添加关键字
     * */
    public function add_keyword(){
        $key = input('key');
        if(empty($key)) $key = '';
        $this->assign('key',$key);
        $this->assign('dis',1);
        $this->assign('replay_arr',[]);
        return $this->fetch();
    }
    /**
     * 修改关键字
     * */
    public function info_keyword(){
        $key = input('key');
        if(empty($key)) return $this->failed('参数错误,请重新修改');
        $this->assign('replay_arr',WechatReply::getDataByKey($key));
        $this->assign('key',$key);
        $this->assign('dis',2);
        return $this->fetch('add_keyword');
    }
    /**
     * 保存关键字
     * */
    public function save_keyword()
    {
        $data = Util::postMore([
            'key',
            'type',
            ['status',0],
            ['data',[]],
        ]);
        if(!isset($data['key']) && empty($data['key']))
            return Json::fail('请输入关键字');
        if(isset($data['key']) && !empty($data['key'])){
            if(trim($data['key']) == 'subscribe') return Json::fail('请重新输入关键字');
            if(trim($data['key']) == 'default') return Json::fail('请重新输入关键字');
        }
        if(!isset($data['type']) && empty($data['type']))
            return Json::fail('请选择回复类型');
        if(!in_array($data['type'],WechatReply::$reply_type))
            return Json::fail('回复类型有误!');
        if(!isset($data['data']) || !is_array($data['data']))
            return Json::fail('回复消息参数有误!');
        $res = WechatReply::redact($data['data'],$data['key'],$data['type'],$data['status']);
        if(!$res)
            return Json::fail(WechatReply::getErrorInfo());
        else
            return Json::successful('保存成功!',$data);
    }

    /**
     * 删除关键字
     * */
    public function delete($id){
        if(!WechatReply::del($id))
            return Json::fail(WechatReply::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }


}