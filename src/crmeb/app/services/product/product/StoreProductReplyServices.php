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

namespace app\services\product\product;


use app\dao\product\product\StoreProductReplyDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class StoreProductReplyService
 * @package app\services\product\product
 * @method int count(array $where = []) 获取条数
 * @method save(array $data) 保存数据
 */
class StoreProductReplyServices extends BaseServices
{
    /**
     * StoreProductReplyServices constructor.
     * @param StoreProductReplyDao $dao
     */
    public function __construct(StoreProductReplyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取评论列表
     * @param array $where
     * @return array
     */
    public function sysPage(array $where)
    {
        /** @var StoreProductReplyStoreProductServices $storeProductReplyStoreProductServices */
        $storeProductReplyStoreProductServices = app()->make(StoreProductReplyStoreProductServices::class);
        $data = $storeProductReplyStoreProductServices->getProductReplyList($where);
        foreach ($data['list'] as &$item) {
            $item['time'] = time_tran(strtotime($item['add_time']));
            $item['create_time'] = $item['add_time'];
            $item['score'] = ($item['product_score'] + $item['service_score']) / 2;
        }
        return $data;
    }

    /**
     * 创建虚拟评论表单
     * @param int $product_id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $product_id)
    {
        if ($product_id == 0) {
            $field[] = Form::frameImage('image', '商品', Url::buildUrl('admin/store.StoreProduct/index', array('fodder' => 'image')))->icon('ios-add')->width('60%')->height('536px')->Props(['srcKey' => 'image']);
        } else {
            $field[] = Form::hidden('product_id', $product_id);
        }
        $field[] = Form::frameImage('avatar', '用户头像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'avatar')))->icon('ios-add')->width('50%')->height('396px');
        $field[] = Form::input('nickname', '用户名称')->col(24);
        $field[] = Form::input('comment', '评价文字')->type('textarea');
        $field[] = Form::rate('product_score', '商品分数', 0)->allowHalf(false);
        $field[] = Form::rate('service_score', '服务分数', 0)->allowHalf(false);
        $field[] = Form::frameImages('pics', '评价图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pics', 'type' => 'many')))->maxLength(10)->icon('ios-add')->width('50%')->height('396px')->props(['closeBtn' => false, 'okBtn' => false]);
        return create_form('添加虚拟评论', $field, Url::buildUrl('/product/reply/save_fictitious_reply'), 'POST');
    }

    /**
     * 添加虚拟评论
     * @param array $data
     */
    public function saveReply(array $data)
    {
        $data['uid'] = 0;
        $data['oid'] = 0;
        $data['unique'] = uniqid();
        $data['reply_type'] = 'product';
        $data['add_time'] = time();
        $data['pics'] = json_encode($data['pics']);
        unset($data['image']);
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException('添加虚拟评论失败');
    }

    /**
     * 回复评论
     * @param int $id
     * @param string $content
     */
    public function setReply(int $id, string $content)
    {
        if ($content == '') throw new AdminException('请输入回复内容');
        $save['merchant_reply_content'] = $content;
        $save['merchant_reply_time'] = time();
        $save['is_reply'] = 1;
        $res = $this->dao->update($id, $save);
        if (!$res) throw new AdminException('回复失败，请稍后再试');
    }

    /**
     * 删除
     * @param int $id
     */
    public function del(int $id)
    {
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException('删除失败');
    }

    /**
     * 获取最近最好的一条评论
     * @param int $productId
     * @return array|\think\Model|null
     */
    public function getRecProductReply(int $productId)
    {
        $res = $this->dao->getProductReply($productId);
        if ($res) {
            $res = $res->toArray();
            $res['cartInfo'] = isset($res['cartInfo']) ? json_decode($res['cartInfo'], true) : [];
            $res['suk'] = isset($res['cartInfo']['productInfo']['attrInfo']) ? $res['cartInfo']['productInfo']['attrInfo']['suk'] : '';
            $res['nickname'] = anonymity($res['nickname']);
            $res['merchant_reply_time'] = date('Y-m-d H:i', $res['merchant_reply_time']);
            $res['add_time'] = time_tran($res['add_time']);
            $res['star'] = bcadd($res['product_score'], $res['service_score'], 2);
            $res['star'] = bcdiv($res['star'], '2', 0);
            $res['comment'] = $res['comment'] ?: '此用户没有填写评价';
            $res['pics'] = is_string($res['pics']) ? json_decode($res['pics'], true) : $res['pics'];
            unset($res['cartInfo']);
        }
        return $res;
    }

    /**
     * 获取好评率
     * @param int $id
     * @return int|string
     */
    public function getProductReplyChance(int $id)
    {
        $replyCount = $this->dao->replyCount($id);
        if ($replyCount) {
            $goodReply = $this->dao->replyCount($id, 1);
            if ($goodReply) {
                $replyCount = bcdiv((string)$goodReply, (string)$replyCount, 2);
                $replyCount = bcmul((string)$replyCount, '100', 0);
            } else {
                $replyCount = 0;
            }
        } else {
            $replyCount = 100;
        }
        return $replyCount;
    }

    /**商品评论数量
     * @return int
     */
    public function replyCount()
    {
        return $this->dao->count(['is_reply' => 0, 'is_del' => 0]);
    }

    /**
     * 获取商品评论数量
     * @param int $id
     * @return mixed
     */
    public function productReplyCount(int $id)
    {
        $data['sum_count'] = $this->dao->replyCount($id);
        $data['good_count'] = $this->dao->replyCount($id, 1);
        $data['in_count'] = $this->dao->replyCount($id, 2);
        $data['poor_count'] = $this->dao->replyCount($id, 3);
        if ($data['sum_count'] != 0) {
            $data['reply_chance'] = bcdiv($data['good_count'], $data['sum_count'], 2);
            $data['reply_star'] = round(bcdiv($this->dao->sum(['product_id' => $id, 'is_del' => 0], 'product_score'), $data['sum_count'], 1));
        } else {
            $data['reply_chance'] = 100;
            $data['reply_star'] = 3;
        }
//        $data['reply_star'] = bcmul($data['reply_chance'], 5, 0);
        $data['reply_chance'] = $data['sum_count'] == 0 ? 100 : bcmul($data['reply_chance'], 100, 0);
        return $data;
    }

    /**
     * 获取商品评论列表
     * @param int $id
     * @param int $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductReplyList(int $id, int $type)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->replyList($id, $type, $page, $limit);
        foreach ($list as &$item) {
            $item['suk'] = isset($item['cart_info']['productInfo']['attrInfo']) ? $item['cart_info']['productInfo']['attrInfo']['suk'] : '';
            $item['nickname'] = anonymity($item['nickname']);
            $item['merchant_reply_time'] = date('Y-m-d H:i', $item['merchant_reply_time']);
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
            $item['star'] = bcadd($item['product_score'], $item['service_score'], 2);
            $item['star'] = bcdiv($item['star'], 2, 0);
            $item['comment'] = $item['comment'] ?: '此用户没有填写评价';
            $item['pics'] = is_string($item['pics']) ? json_decode($item['pics'], true) : $item['pics'];
            unset($item['cart_info']);
        }
        return $list;
    }
}
