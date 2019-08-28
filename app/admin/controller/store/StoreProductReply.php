<?php

namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use crmeb\services\JsonService;
use crmeb\services\UtilService;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use app\admin\model\store\StoreProductReply as ProductReplyModel;
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
        $product_id = input('product_id');
        if(!$product_id) $product_id =  0;
        $this->assign('is_layui',true);
        $this->assign('product_id',(int)$product_id);
        return $this->fetch();
    }

    public function get_product_imaes_list()
    {
        $where=UtilService::getMore([
            ['page',1],
            ['limit',10],
            ['title',''],
            ['is_reply',''],
            ['product_name',''],
            ['product_id',0],
        ]);
        return JsonService::successful(ProductReplyModel::getProductImaesList($where));
    }

    public function get_product_reply_list()
    {
        $where=UtilService::getMore([
            ['limit',10],
            ['title',''],
            ['is_reply',''],
            ['message_page',1],
            ['producr_id',0],
        ]);
        return JsonService::successful(ProductReplyModel::getProductReplyList($where));
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

    public function set_reply(){
        $data = Util::postMore([
            'id',
            'content',
        ]);
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

    public function edit_reply(){
        $data = Util::postMore([
            'id',
            'content',
        ]);
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
