<?php

namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\store\StoreProductReply as ProductReplyModel;
use think\Url;
/**
 * 评论管理 控制器
 * Class StoreProductReply
 * @package app\admin\controller\store
 */
class StoreProductReply extends AuthController
{

    use CurdControllerTrait;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['is_reply',''],
            ['comment',''],
        ],$this->request);
        $product_id = 0;
        $product_id = input('product_id');
        if($product_id)
           $where['product_id'] =  $product_id;
        else
            $where['product_id'] =  0;
        $this->assign('where',$where);
        $this->assign(ProductReplyModel::systemPage($where));
        return $this->fetch();
    }

    /**
     * @param $id
     * @return \think\response\Json|void
     */
    public function delete($id){
        if(!$id) return $this->failed('数据不存在');
        $data['is_del'] = 1;
        if(!ProductReplyModel::edit($data,$id))
            return Json::fail(ProductReplyModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    public function set_reply(Request $request){
        $data = Util::postMore([
            'id',
            'content',
        ],$request);
        if(!$data['id']) return Json::fail('参数错误');
        if($data['content'] == '') return Json::fail('请输入回复内容');
        $save['merchant_reply_content'] = $data['content'];
        $save['merchant_reply_time'] = time();
        $save['is_reply'] = 2;
        $res = ProductReplyModel::edit($save,$data['id']);
        if(!$res)
            return Json::fail(ProductReplyModel::getErrorInfo('回复失败,请稍候再试!'));
        else
            return Json::successful('回复成功!');
    }

    public function edit_reply(Request $request){
        $data = Util::postMore([
            'id',
            'content',
        ],$request);
        if(!$data['id']) return Json::fail('参数错误');
        if($data['content'] == '') return Json::fail('请输入回复内容');
        $save['merchant_reply_content'] = $data['content'];
        $save['merchant_reply_time'] = time();
        $save['is_reply'] = 2;
        $res = ProductReplyModel::edit($save,$data['id']);
        if(!$res)
            return Json::fail(ProductReplyModel::getErrorInfo('回复失败,请稍候再试!'));
        else
            return Json::successful('回复成功!');
    }

}
