<?php

namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use app\models\store\StoreOrder;
use crmeb\traits\CurdControllerTrait;
use think\facade\Route as Url;
use app\admin\model\store\StoreProductReply as ProductReplyModel;
use crmeb\services\{
    FormBuilder as Form, JsonService as Json, UtilService as Util
};

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
        if (!$product_id) $product_id = 0;
        $this->assign('is_layui', true);
        $this->assign('product_id', (int)$product_id);
        return $this->fetch();
    }

    public function get_product_imaes_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 10],
            ['title', ''],
            ['is_reply', ''],
            ['product_name', ''],
            ['product_id', 0],
        ]);
        return Json::successful(ProductReplyModel::getProductImaesList($where));
    }

    public function get_product_reply_list()
    {
        $where = Util::getMore([
            ['limit', 10],
            ['title', ''],
            ['is_reply', ''],
            ['message_page', 1],
            ['producr_id', 0],
            ['order_id', ''],
            ['nickname', ''],
            ['score_type', ''],
        ]);
        return Json::successful(ProductReplyModel::getProductReplyList($where));
    }

    /**
     * @param $id
     * @return \think\response\Json|void
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $data['is_del'] = 1;
        if (!ProductReplyModel::edit($data, $id))
            return Json::fail(ProductReplyModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    public function set_reply()
    {
        $data = Util::postMore([
            'id',
            'content',
        ]);
        if (!$data['id']) return Json::fail('参数错误');
        if ($data['content'] == '') return Json::fail('请输入回复内容');
        $save['merchant_reply_content'] = $data['content'];
        $save['merchant_reply_time'] = time();
        $save['is_reply'] = 2;
        $res = ProductReplyModel::edit($save, $data['id']);
        if (!$res)
            return Json::fail(ProductReplyModel::getErrorInfo('回复失败,请稍候再试!'));
        else
            return Json::successful('回复成功!');
    }

    public function edit_reply()
    {
        $data = Util::postMore([
            'id',
            'content',
        ]);
        if (!$data['id']) return Json::fail('参数错误');
        if ($data['content'] == '') return Json::fail('请输入回复内容');
        $save['merchant_reply_content'] = $data['content'];
        $save['merchant_reply_time'] = time();
        $save['is_reply'] = 2;
        $res = ProductReplyModel::edit($save, $data['id']);
        if (!$res)
            return Json::fail(ProductReplyModel::getErrorInfo('回复失败,请稍候再试!'));
        else
            return Json::successful('回复成功!');
    }

    /**
     * 添加虚拟评论表单
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $field = [
            Form::frameImageOne('image', '商品', Url::buildUrl('admin/store.StoreProductReply/select', array('fodder' => 'image')))->icon('plus')->width('100%')->height('500px'),
            Form::hidden('product_id', 0),
            Form::input('nickname', '用户名称')->col(Form::col(24)),
            Form::input('comment', '评价文字')->type('textarea'),
            Form::number('product_score', '商品分数')->col(8)->value(5)->min(1)->max(5),
            Form::number('service_score', '服务分数')->col(8)->value(5)->min(1)->max(5),
            Form::frameImageOne('avatar', '用户头像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'avatar')))->icon('image')->width('100%')->height('500px'),
            Form::frameImages('pics', '评价图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pics')))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0)
        ];
        $form = Form::make_post_form('添加评论', $field, Url::buildUrl('save'), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 选择商品
     * @param int $id
     */
    public function select()
    {
        return $this->fetch();
    }

    /**
     * 保存评论
     */
    public function save()
    {
        $data = Util::postMore([
            ['nickname', ''],
            ['avatar', ''],
            ['product_id', ''],
            ['product_score', 0],
            ['service_score', 0],
            ['comment', ''],
            ['pics', []],
        ]);
        if ($data['product_id'] == 0) return Json::fail('请选择商品');
        if ($data['nickname'] == '') return Json::fail('请填写用户名称');
        if ($data['comment'] == '') return Json::fail('请填写评价');
        if ($data['avatar'] == '') return Json::fail('请选择用户头像');
        $data['uid'] = 0;
        $data['oid'] = 0;
        $data['unique'] = uniqid();
        $data['reply_type'] = 'product';
        $data['add_time'] = time();
        $data['pics'] = json_encode($data['pics']);
        ProductReplyModel::create($data);
        return Json::successful('添加成功!');
    }

}
