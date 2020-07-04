<?php

namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use app\admin\model\store\{
    StoreDescription,
    StoreProductAttrValue,
    StoreProductAttr,
    StoreProductAttrResult,
    StoreProductCate,
    StoreProductRelation,
    StoreCategory as CategoryModel,
    StoreProduct as ProductModel
};
use app\admin\model\ump\StoreBargain;
use app\admin\model\ump\StoreCombination;
use app\admin\model\ump\StoreSeckill;
use crmeb\services\{
    JsonService, UtilService as Util, JsonService as Json, FormBuilder as Form
};
use crmeb\traits\CurdControllerTrait;
use think\facade\Route as Url;
use app\admin\model\system\{
    SystemAttachment, ShippingTemplates
};


/**
 * 产品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class StoreProduct extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = ProductModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $type = $this->request->param('type', 1);
        //获取分类
        $this->assign('cate', CategoryModel::getTierList(null, 1));
        //出售中产品
        $onsale = ProductModel::where('is_del', 0)->where('is_show', 1)->count();
        //待上架产品
        $forsale = ProductModel::where('is_del', 0)->where('is_show', 0)->count();
        //仓库中产品
        $warehouse = ProductModel::where('is_del', 0)->count();
        //已经售馨产品
        $outofstock = ProductModel::getModelObject(['type' => 4])->count();
        //警戒库存
        $policeforce = ProductModel::getModelObject(['type' => 5])->count();
        //回收站
        $recycle = ProductModel::where('is_del', 1)->count();
        $this->assign(compact('type', 'onsale', 'forsale', 'warehouse', 'outofstock', 'policeforce', 'recycle'));
        return $this->fetch();
    }

    /**
     * 异步查找产品
     *
     * @return json
     */
    public function product_ist()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['store_name', ''],
            ['cate_id', ''],
            ['excel', 0],
            ['order', ''],
            [['type', 'd'], $this->request->param('type/d')]
        ]);
        return Json::successlayui(ProductModel::ProductList($where));
    }

    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && Json::fail('缺少参数');
        $res = ProductModel::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return Json::successful($is_show == 1 ? '上架成功' : '下架成功');
        } else {
            return Json::fail($is_show == 1 ? '上架失败' : '下架失败');
        }
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_product($field = '', $id = '', $value = '')
    {
        $field == '' || $id == '' || $value == '' && Json::fail('缺少参数');
        if (ProductModel::where(['id' => $id])->update([$field => $value]))
            return Json::successful('保存成功');
        else
            return Json::fail('保存失败');
    }

    /**
     * 设置批量产品上架
     *
     * @return json
     */
    public function product_show()
    {
        $post = Util::postMore([
            ['ids', []]
        ]);
        if (empty($post['ids'])) {
            return Json::fail('请选择需要上架的产品');
        } else {
            $res = ProductModel::where('id', 'in', $post['ids'])->update(['is_show' => 1]);
            if ($res)
                return Json::successful('上架成功');
            else
                return Json::fail('上架失败');
        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create($id = 0)
    {
        $this->assign('id', (int)$id);
        return $this->fetch();
    }

    /**
     * 获取规则属性模板
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_rule()
    {
        return Json::successful(\app\models\store\StoreProductRule::field(['rule_name', 'rule_value'])->select()->each(function ($item) {
            $item['rule_value'] = json_decode($item['rule_value'], true);
        })->toArray());
    }

    /**
     * 获取产品详细信息
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_product_info($id = 0)
    {
        $list = CategoryModel::getTierList(null, 1);
        $menus = [];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0 ? 0 : 1];//,'disabled'=>$menu['pid']== 0];
        }
        $data['tempList'] = ShippingTemplates::order('sort', 'desc')->field(['id', 'name'])->select()->toArray();
        $data['cateList'] = $menus;
        $data['productInfo'] = [];
        if ($id) {
            $productInfo = ProductModel::get($id);
            if (!$productInfo) {
                return Json::fail('修改的产品不存在');
            }
            $productInfo['cate_id'] = explode(',', $productInfo['cate_id']);
            $productInfo['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id));
            $productInfo['slider_image'] = is_string($productInfo['slider_image']) ? json_decode($productInfo['slider_image'], true) : [];
            if ($productInfo['spec_type'] == 1) {
                $result = StoreProductAttrResult::getResult($id);
                foreach ($result['value'] as $k => $v) {
                    $num = 1;
                    foreach ($v['detail'] as $dv) {
                        $result['value'][$k]['value' . $num] = $dv;
                        $num++;
                    }
                }
                $productInfo['items'] = $result['attr'];
                $productInfo['attrs'] = $result['value'];
                $productInfo['attr'] = ['pic' => '', 'price' => 0, 'cost' => 0, 'ot_price' => 0, 'stock' => 0, 'bar_code' => '', 'weight' => 0, 'volume' => 0, 'brokerage' => 0, 'brokerage_two' => 0];
            } else {
                $result = StoreProductAttrValue::where('product_id', $id)->find();
                if ($result) {
                    $single = $result->toArray();
                } else {
                    $single = [];
                }
                $productInfo['items'] = [];
                $productInfo['attrs'] = [];
                $productInfo['attr'] = [
                    'pic' => $single['image'] ?? '',
                    'price' => $single['price'] ?? 0,
                    'cost' => $single['cost'] ?? 0,
                    'ot_price' => $single['ot_price'] ?? 0,
                    'stock' => $single['stock'] ?? 0,
                    'bar_code' => $single['bar_code'] ?? '',
                    'weight' => $single['weight'] ?? 0,
                    'volume' => $single['volume'] ?? 0,
                    'brokerage' => $single['brokerage'] ?? 0,
                    'brokerage_two' => $single['brokerage_two'] ?? 0,
                ];
            }
            if ($productInfo['activity']) {
                $activity = explode(',', $productInfo['activity']);
                foreach ($activity as $k => $v) {
                    if ($v == 1) {
                        $activity[$k] = '秒杀';
                    } elseif ($v == 2) {
                        $activity[$k] = '砍价';
                    } elseif ($v == 3) {
                        $activity[$k] = '拼团';
                    }
                }
                $productInfo['activity'] = $activity;
            } else {
                $productInfo['activity'] = ['秒杀', '砍价', '拼团'];
            }
            $data['productInfo'] = $productInfo;
        }
        return JsonService::successful($data);
    }

    /**
     * 保存新建的资源
     *
     *
     */
    public function save($id)
    {
        $data = Util::postMore([
            ['cate_id', []],
            'store_name',
            'store_info',
            'keyword',
            ['unit_name', '件'],
            ['image', []],
            ['slider_image', []],
            ['postage', 0],
            ['is_sub', 0],
            ['sort', 0],
            ['sales', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['temp_id', 0],
            ['is_hot', 0],
            ['is_benefit', 0],
            ['is_best', 0],
            ['is_new', 0],
            ['mer_use', 0],
            ['is_postage', 0],
            ['is_good', 0],
            ['description', ''],
            ['spec_type', 0],
            ['video_link', ''],
            ['items', []],
            ['attrs', []],
            ['activity', []]
        ]);
        foreach ($data['activity'] as $k => $v) {
            if ($v == '秒杀') {
                $data['activity'][$k] = 1;
            } elseif ($v == '砍价') {
                $data['activity'][$k] = 2;
            } else {
                $data['activity'][$k] = 3;
            }
        }
        $data['activity'] = implode(',', $data['activity']);
        $detail = $data['attrs'];
        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['cost'] = min(array_column($detail, 'cost'));
        $attr = $data['items'];
        unset($data['items'], $data['video'], $data['attrs']);
        if (count($data['cate_id']) < 1) return Json::fail('请选择产品分类');
        $cate_id = $data['cate_id'];
        $data['cate_id'] = implode(',', $data['cate_id']);
        if (!$data['store_name']) return Json::fail('请输入产品名称');
        if (count($data['image']) < 1) return Json::fail('请上传产品图片');
        if (count($data['slider_image']) < 1) return Json::fail('请上传产品轮播图');
        $data['image'] = $data['image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        ProductModel::beginTrans();
        foreach ($detail as &$item) {
            if (($item['brokerage'] + $item['brokerage_two']) > $item['price']) {
                return Json::fail('一二级返佣相加不能大于商品售价');
            }
        }
        if ($id) {
            unset($data['sales']);
            ProductModel::edit($data, $id);
            $description = $data['description'];
            unset($data['description']);
            StoreDescription::saveDescription($description, $id);
            StoreProductCate::where('product_id', $id)->delete();
            $cateData = [];
            foreach ($cate_id as $cid) {
                $cateData[] = ['product_id' => $id, 'cate_id' => $cid, 'add_time' => time()];
            }
            StoreProductCate::insertAll($cateData);
            if ($data['spec_type'] == 0) {
                $attr = [
                    [
                        'value' => '规格',
                        'detailValue' => '',
                        'attrHidden' => '',
                        'detail' => ['默认']
                    ]
                ];
                $detail[0]['value1'] = '规格';
                $detail[0]['detail'] = ['规格' => '默认'];
            }

            $attr_res = StoreProductAttr::createProductAttr($attr, $detail, $id);
            if ($attr_res) {
                ProductModel::commitTrans();
                return Json::success('修改成功!');
            } else {
                ProductModel::rollbackTrans();
                return Json::fail(StoreProductAttr::getErrorInfo());
            }
        } else {
            $data['add_time'] = time();
            $data['code_path'] = '';
            $res = ProductModel::create($data);
            $description = $data['description'];
            StoreDescription::saveDescription($description, $res['id']);
            $cateData = [];
            foreach ($cate_id as $cid) {
                $cateData[] = ['product_id' => $res['id'], 'cate_id' => $cid, 'add_time' => time()];
            }
            StoreProductCate::insertAll($cateData);
            if ($data['spec_type'] == 0) {
                $attr = [
                    [
                        'value' => '规格',
                        'detailValue' => '',
                        'attrHidden' => '',
                        'detail' => ['默认']
                    ]
                ];
                $detail[0]['value1'] = '规格';
                $detail[0]['detail'] = ['规格' => '默认'];
            }
            $attr_res = StoreProductAttr::createProductAttr($attr, $detail, $res['id']);
            if ($attr_res) {
                ProductModel::commitTrans();
                return Json::success('添加产品成功!');
            } else {
                ProductModel::rollbackTrans();
                return Json::fail(StoreProductAttr::getErrorInfo());
            }
        }
    }


    public function edit_content($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $this->assign([
            'content' => $product->description,
            'field' => 'description',
            'action' => Url::buildUrl('change_field', ['id' => $id, 'field' => 'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $field = [
            Form::select('cate_id', '产品分类', explode(',', $product->getData('cate_id')))->setOptions(function () {
                $list = CategoryModel::getTierList(null, 1);
                $menus = [];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0];//,'disabled'=>$menu['pid']== 0];
                }
                return $menus;
            })->filterable(1)->multiple(1),
            Form::input('store_name', '产品名称', $product->getData('store_name')),
            Form::input('store_info', '产品简介', $product->getData('store_info'))->type('textarea'),
            Form::input('keyword', '产品关键字', $product->getData('keyword'))->placeholder('多个用英文状态下的逗号隔开'),
            Form::input('unit_name', '产品单位', $product->getData('unit_name'))->col(12),
            Form::input('bar_code', '产品条码', $product->getData('bar_code'))->col(12),
            Form::frameImageOne('image', '产品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px'),
            Form::frameImages('slider_image', '产品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'slider_image')), json_decode($product->getData('slider_image'), 1) ?: [])->maxLength(5)->icon('images')->width('100%')->height('500px'),
            Form::number('price', '产品售价', $product->getData('price'))->min(0)->col(8),
            Form::number('ot_price', '产品市场价', $product->getData('ot_price'))->min(0)->col(8),
            Form::number('give_integral', '赠送积分', $product->getData('give_integral'))->min(0)->col(8),
            Form::number('postage', '邮费', $product->getData('postage'))->min(0)->col(8),
            Form::number('sales', '销量', $product->getData('sales'))->min(0)->precision(0)->col(8)->readonly(1),
            Form::number('ficti', '虚拟销量', $product->getData('ficti'))->min(0)->precision(0)->col(8),
            Form::number('stock', '库存', ProductModel::getStock($id) > 0 ? ProductModel::getStock($id) : $product->getData('stock'))->min(0)->precision(0)->col(8),
            Form::number('cost', '产品成本价', $product->getData('cost'))->min(0)->col(8),
            Form::number('sort', '排序', $product->getData('sort'))->col(8),
            Form::radio('is_show', '产品状态', $product->getData('is_show'))->options([['label' => '上架', 'value' => 1], ['label' => '下架', 'value' => 0]])->col(8),
            Form::radio('is_hot', '热卖单品', $product->getData('is_hot'))->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('is_benefit', '促销单品', $product->getData('is_benefit'))->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('is_best', '精品推荐', $product->getData('is_best'))->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('is_new', '首发新品', $product->getData('is_new'))->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('is_postage', '是否包邮', $product->getData('is_postage'))->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('is_good', '是否优品推荐', $product->getData('is_good'))->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
        ];
        $form = Form::make_post_form('编辑产品', $field, Url::buildUrl('update', array('id' => $id)), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的资源
     *
     * @param $id
     */
    public function update($id)
    {
        $data = Util::postMore([
            ['cate_id', []],
            'store_name',
            'store_info',
            'keyword',
            'bar_code',
            ['unit_name', '件'],
            ['image', []],
            ['slider_image', []],
            ['postage', 0],
            ['ot_price', 0],
            ['price', 0],
            ['sort', 0],
            ['stock', 0],
            ['temp_id', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['cost', 0],
            ['is_hot', 0],
            ['is_benefit', 0],
            ['is_best', 0],
            ['is_new', 0],
            ['mer_use', 0],
            ['is_postage', 0],
            ['is_good', 0],
        ]);
        if (count($data['cate_id']) < 1) return Json::fail('请选择产品分类');
        $cate_id = $data['cate_id'];
        $data['cate_id'] = implode(',', $data['cate_id']);
        if (!$data['store_name']) return Json::fail('请输入产品名称');
        if (count($data['image']) < 1) return Json::fail('请上传产品图片');
        if (count($data['slider_image']) < 1) return Json::fail('请上传产品轮播图');
        // if(count($data['slider_image'])>8) return Json::fail('轮播图最多5张图');
        if ($data['price'] == '' || $data['price'] < 0) return Json::fail('请输入产品售价');
        if ($data['ot_price'] == '' || $data['ot_price'] < 0) return Json::fail('请输入产品市场价');
        if ($data['stock'] == '' || $data['stock'] < 0) return Json::fail('请输入库存');
        $data['image'] = $data['image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        ProductModel::edit($data, $id);
        StoreProductCate::where('product_id', $id)->delete();
        foreach ($cate_id as $cid) {
            StoreProductCate::insert(['product_id' => $id, 'cate_id' => $cid, 'add_time' => time()]);
        }
        return Json::successful('修改成功!');
    }

    public function attr($id)
    {
        if (!$id) return $this->failed('数据不存在!');
//        $result = StoreProductAttrResult::getResult($id);
        $result = StoreProductAttrValue::getStoreProductAttrResult($id);
        $image = ProductModel::where('id', $id)->value('image');
        $this->assign(compact('id', 'result', 'image'));
        return $this->fetch();
    }

    /**
     * 生成属性
     * @param int $id
     */
    public function is_format_attr($id = 0, $type = 0)
    {
        $data = Util::postMore([
            ['attrs', []],
            ['items', []]
        ]);
        $attr = $data['attrs'];
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $types = 1;
            if ($id) {
                $sukValue = StoreProductAttrValue::where('product_id', $id)->where('type', 0)->where('suk', $suk)->column('bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two', 'suk');
                if (!count($sukValue)) {
                    if ($type == 0) $types = 0; //编辑商品时，将没有规格的数据不生成默认值
                    $sukValue[$suk]['pic'] = '';
                    $sukValue[$suk]['price'] = 0;
                    $sukValue[$suk]['cost'] = 0;
                    $sukValue[$suk]['ot_price'] = 0;
                    $sukValue[$suk]['stock'] = 0;
                    $sukValue[$suk]['bar_code'] = '';
                    $sukValue[$suk]['weight'] = 0;
                    $sukValue[$suk]['volume'] = 0;
                    $sukValue[$suk]['brokerage'] = 0;
                    $sukValue[$suk]['brokerage_two'] = 0;
                }
            } else {
                $sukValue[$suk]['pic'] = '';
                $sukValue[$suk]['price'] = 0;
                $sukValue[$suk]['cost'] = 0;
                $sukValue[$suk]['ot_price'] = 0;
                $sukValue[$suk]['stock'] = 0;
                $sukValue[$suk]['bar_code'] = '';
                $sukValue[$suk]['weight'] = 0;
                $sukValue[$suk]['volume'] = 0;
                $sukValue[$suk]['brokerage'] = 0;
                $sukValue[$suk]['brokerage_two'] = 0;
            }
            if ($types) { //编辑商品时，将没有规格的数据不生成默认值
                foreach (array_keys($detail) as $k => $title) {
                    $header[$k]['title'] = $title;
                    $header[$k]['align'] = 'center';
                    $header[$k]['minWidth'] = 130;
                }
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                    $header[$k]['key'] = 'value' . ($k + 1);
                }
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ?? 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ?? 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ?? 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ?? 0;
                $count++;
            }
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '售价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '成本价', 'slot' => 'cost', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '原价', 'slot' => 'ot_price', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '库存', 'slot' => 'stock', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '产品编号', 'slot' => 'bar_code', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '重量(KG)', 'slot' => 'weight', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '体积(m³)', 'slot' => 'volume', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '操作', 'slot' => 'action', 'align' => 'center', 'minWidth' => 70];
        $info = ['attr' => $attr, 'value' => $valueNew, 'header' => $header];
        return Json::successful($info);
    }

    public function set_attr($id)
    {
        if (!$id) return $this->failed('产品不存在!');
        list($attr, $detail) = Util::postMore([
            ['items', []],
            ['attrs', []]
        ], null, true);
        $res = StoreProductAttr::createProductAttr($attr, $detail, $id);
        if ($res)
            return $this->successful('编辑属性成功!');
        else
            return $this->failed(StoreProductAttr::getErrorInfo());
    }

    public function clear_attr($id)
    {
        if (!$id) return $this->failed('产品不存在!');
        if (false !== StoreProductAttr::clearProductAttr($id) && false !== StoreProductAttrResult::clearResult($id))
            return $this->successful('清空产品属性成功!');
        else
            return $this->failed(StoreProductAttr::getErrorInfo('清空产品属性失败!'));
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {

        if (!$id) return $this->failed('数据不存在');
        if (!ProductModel::be(['id' => $id])) return $this->failed('产品数据不存在');
        if (ProductModel::be(['id' => $id, 'is_del' => 1])) {
            $data['is_del'] = 0;
            if (!ProductModel::edit($data, $id))
                return Json::fail(ProductModel::getErrorInfo('恢复失败,请稍候再试!'));
            else
                return Json::successful('成功恢复产品!');
        } else {
            $res1 = StoreSeckill::where('product_id', $id)->where('is_del', 0)->find();
            $res2 = StoreBargain::where('product_id', $id)->where('is_del', 0)->find();
            $res3 = StoreCombination::where('product_id', $id)->where('is_del', 0)->find();
            if ($res1 || $res2 || $res3) {
                return Json::fail(ProductModel::getErrorInfo('该商品已参加活动，无法删除!'));
            } else {
                $data['is_del'] = 1;
                if (!ProductModel::edit($data, $id))
                    return Json::fail(ProductModel::getErrorInfo('删除失败,请稍候再试!'));
                else
                    return Json::successful('成功移到回收站!');
            }

        }

    }


    /**
     * 点赞
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function collect($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $this->assign(StoreProductRelation::getCollect($id));
        return $this->fetch();
    }

    /**
     * 收藏
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function like($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $this->assign(StoreProductRelation::getLike($id));
        return $this->fetch();
    }

    /**
     * 修改产品价格
     */
    public function edit_product_price()
    {
        $data = Util::postMore([
            ['id', 0],
            ['price', 0],
        ]);
        if (!$data['id']) return Json::fail('参数错误');
        $res = ProductModel::edit(['price' => $data['price']], $data['id']);
        if ($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }

    /**
     * 修改产品库存
     *
     */
    public function edit_product_stock()
    {
        $data = Util::postMore([
            ['id', 0],
            ['stock', 0],
        ]);
        if (!$data['id']) return Json::fail('参数错误');
        $res = ProductModel::edit(['stock' => $data['stock']], $data['id']);
        if ($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }

    /**
     * 检测商品是否开活动
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check_activity($id)
    {
        if ($id != 0) {
            $res1 = StoreSeckill::where('product_id', $id)->where('is_del', 0)->find();
            $res2 = StoreBargain::where('product_id', $id)->where('is_del', 0)->find();
            $res3 = StoreCombination::where('product_id', $id)->where('is_del', 0)->find();
            if ($res1 || $res2 || $res3) {
                return Json::successful('该商品有活动开启，无法删除属性');
            } else {
                return Json::fail('删除成功');
            }
        } else {
            return Json::fail('没有参数ID');
        }
    }
}
