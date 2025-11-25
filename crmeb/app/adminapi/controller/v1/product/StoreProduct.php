<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\product;


use app\adminapi\controller\AuthController;
use app\adminapi\controller\v1\system\SystemClearData;
use app\services\order\StoreCartServices;
use app\services\other\CacheServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use crmeb\services\FileService;
use app\services\other\UploadService;
use think\facade\App;
use think\Request;

/**
 * Class StoreProduct
 * @package app\adminapi\controller\v1\product
 */
class StoreProduct extends AuthController
{
    protected $service;

    public function __construct(App $app, StoreProductServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 显示资源列表头部
     * @return mixed
     */
    public function type_header()
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['spec_type', ''],
            ['is_gift', ''],
            ['vip_product', ''],
            ['price_s', []],
            ['stock_s', []],
            ['sales_s', []],
        ]);
        $list = $this->service->getHeader($where);
        return app('json')->success(compact('list'));
    }

    /**
     * 获取退出未保存的数据
     * @param CacheServices $services
     * @return mixed
     */
    public function getCacheData(CacheServices $services)
    {
        return app('json')->success(['info' => $services->getDbCache($this->adminId . '_product_data', [])]);
    }

    /**
     * 1分钟保存一次产品数据
     * @param CacheServices $services
     * @return mixed
     */
    public function saveCacheData(CacheServices $services)
    {
        $data = $this->request->postMore([
            ['cate_id', []],
            ['store_name', ''],
            ['store_info', ''],
            ['keyword', ''],
            ['unit_name', '件'],
            ['image', []],
            ['recommend_image', ''],
            ['slider_image', []],
            ['postage', 0],
            ['is_sub', []],//佣金是单独还是默认
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
            ['activity', []],
            ['coupon_ids', []],
            ['label_id', []],
            ['command_word', ''],
            ['tao_words', ''],
            ['type', 0]
        ]);
        $services->setDbCache($this->adminId . '_product_data', $data, 68400);
        return app('json')->success(100000);
    }

    /**
     * 删除数据缓存
     * @param CacheServices $services
     * @return mixed
     */
    public function deleteCacheData(CacheServices $services)
    {
        $services->delectDbCache($this->adminId . '_product_data');
        return app('json')->success(100002);
    }

    /**
     * 显示资源列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['type', 1],
            ['sales', 'normal'],
            ['spec_type', ''],
            ['is_gift', ''],
            ['vip_product', ''],
            ['price_s', []],
            ['stock_s', []],
            ['sales_s', []],
            ['store_label_id', []],
            ['logistics', ''],
            ['time', ''],
            ['virtual_type', ''],
        ]);
        $data = $this->service->getList($where);
        return app('json')->success($data);
    }

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     * @return mixed
     */
    public function set_show($is_show = '', $id = '')
    {
        $del = $this->service->value(['id' => $id], 'is_del');
        if ($del == 1) return app('json')->fail('商品已删除，请先恢复商品');
        $this->service->setShow([$id], $is_show);
        return app('json')->success(100014);
    }

    /**
     * 设置批量商品上架
     * @return mixed
     */
    public function product_show()
    {
        [$ids] = $this->request->postMore([
            ['ids', []]
        ], true);
        $this->service->setShow($ids, 1);
        return app('json')->success(100014);
    }

    /**
     * 设置批量商品下架
     * @return mixed
     */
    public function product_unshow()
    {
        [$ids] = $this->request->postMore([
            ['ids', []]
        ], true);
        $this->service->setShow($ids, 0);
        return app('json')->success(100014);
    }

    /**
     * 获取规格模板
     * @return mixed
     */
    public function get_rule()
    {
        $list = $this->service->getRule();
        return app('json')->success($list);
    }

    /**
     * 获取商品详细信息
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_product_info($id = 0)
    {
        return app('json')->success($this->service->getInfo((int)$id));
    }

    /**
     * 保存新建或编辑
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            ['virtual_type', 0],// 商品类型
            ['cate_id', []],//分类id
            ['store_name', ''],//商品名称
            ['keyword', ''],//关键字
            ['unit_name', '件'],//单位
            ['store_info', ''],//商品简介
            ['slider_image', []],//轮播图
            ['video_open', 0],//是否开启视频
            ['video_link', ''],//视频链接
            ['spec_type', 0],//单多规格
            ['items', []],//规格
            ['attrs', []],//规格
            ['description', ''],//商品详情
            ['description_images', []],//商品详情
            ['logistics', []],//物流方式
            ['freight', 1],//运费设置
            ['postage', 0],//邮费
            ['temp_id', 0],//运费模版id
            ['give_integral', 0],//赠送积分
            ['presale', 0],//预售商品开关
            ['presale_time', 0],//预售时间
            ['presale_day', 0],//预售发货日
            ['vip_product', 0],//是否付费会员商品
            ['is_sub', []],//佣金是单独还是默认
            ['recommend', []],//商品推荐
            ['activity', []],//活动优先级
            ['recommend_list', []],//优品推荐商品
            ['coupon_ids', []],//优惠券
            ['label_id', []],//用户标签
            ['command_word', ''],//商品口令
            ['is_show', 0],//是否上架
            ['ficti', 0],//虚拟销量
            ['sort', 0],//排序
            ['recommend_image', ''],//商品推荐图
            ['sales', 0],//销量
            ['custom_form', []],//自定义表单
            ['type', 0],
            ['is_copy', 0],//是否是复制商品
            ['is_limit', 0],//是否限购
            ['limit_type', 0],//限购类型
            ['limit_num', 0],//限购数量
            ['min_qty', 1],//起购数量
            ['params_list', []],//商品参数
            ['label_list', []],//商品标签
            ['protection_list', []],//商品保障
            ['is_gift', 0],//是否礼品
            ['gift_price', 0],//礼品附加费
        ]);
        $this->service->save((int)$id, $data);
        return app('json')->success(100000);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除商品检测是否有参与活动
        $this->service->checkActivity($id);
        $res = $this->service->del($id);
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        $cartService->changeStatus($id, 0);
        return app('json')->success($res);
    }

    /**
     * 批量移动到回收站
     * @return \think\Response
     */
    public function batchDelete()
    {
        [$ids] = $this->request->postMore([
            ['ids', []],
        ], true);
        return app('json')->success($this->service->batchDelete($ids));
    }

    /**
     * 批量从回收站恢复
     * @return \think\Response
     */
    public function batchRecover()
    {
        [$ids] = $this->request->postMore([
            ['ids', []],
        ], true);
        $this->service->batchRecover($ids);
        return app('json')->success('恢复成功');
    }

    /**
     * 生成规格列表
     * @param int $id
     * @param int $type
     * @return mixed
     */
    public function is_format_attr($id = 0, $type = 0)
    {
        $data = $this->request->postMore([
            ['attrs', []],
            ['items', []],
            ['is_virtual', 0],
            ['virtual_type', 0]
        ]);
        $info = $this->service->getAttr($data, $id, $type);
        return app('json')->success(compact('info'));
    }


    /**
     * 获取选择的商品列表
     * @return mixed
     */
    public function search_list()
    {
        $where = $this->request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 1],
            ['is_live', 0],
            ['is_new', ''],
            ['is_virtual', -1],
            ['is_presale', -1],
            ['is_show', 1],
        ]);
        $where['is_del'] = 0;
        $where['cate_id'] = toIntArray($where['cate_id']);
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        if ($where['cate_id'] !== '') {
            if ($storeCategoryServices->value(['id' => $where['cate_id']], 'pid')) {
                $where['sid'] = $where['cate_id'];
            } else {
                $where['cid'] = $where['cate_id'];
            }
        }
        unset($where['cate_id']);
        $list = $this->service->searchList($where);
        return app('json')->success($list);
    }

    /**
     * 获取某个商品规格
     * @return mixed
     */
    public function get_attrs()
    {
        [$id, $type] = $this->request->getMore([
            [['id', 'd'], 0],
            [['type', 'd'], 0],
        ], true);
        $info = $this->service->getProductRules($id, $type);
        return app('json')->success(compact('info'));
    }

    /**
     * 获取运费模板列表
     * @return mixed
     */
    public function get_template()
    {
        return app('json')->success($this->service->getTemp());
    }

    /**
     * 获取视频上传token
     * @return mixed
     * @throws \Exception
     */
    public function getTempKeys(Request $request)
    {
        $upload = UploadService::init();
        $type = (int)sys_config('upload_type', 1);
        $key = $request->get('key', '');
        $path = $request->get('path', '');
        $contentType = $request->get('contentType', '');
        if ($type === 5) {
            if (!$key || !$contentType) {
                return app('json')->fail('缺少参数');
            }
            $re = $upload->getTempKeys($key, $path, $contentType);
        } else {
            $re = $upload->getTempKeys();
        }
        return $re ? app('json')->success($re) : app('json')->fail(100016);
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
        $this->service->checkActivity($id);
        return app('json')->success(100002);
    }

    /**
     * 导入卡密
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function import_card()
    {
        $data = $this->request->getMore([
            ['file', ""]
        ]);
        if (!$data['file']) return app('json')->fail(400168);
        $file = public_path() . substr($data['file'], 1);
        // 获取文件后缀
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($suffix, ['xls', 'xlsx'])) {
            return app('json')->fail('文件格式不正确，请上传xls或xlsx格式的文件！');
        }
        /** @var FileService $readExcelService */
        $readExcelService = app()->make(FileService::class);
        $cardData = $readExcelService->readExcel($file, 'card', 1, ucfirst($suffix));
        return app('json')->success($cardData);
    }

    /**
     * 商品批量设置
     * @return mixed
     */
    public function batchSetting()
    {
        $data = $this->request->postMore([
            ['ids', []],
            ['cate_id', []],
            ['logistics', []],
            ['freight', 2],
            ['postage', 0],
            ['temp_id', 1],
            ['give_integral', 0],
            ['coupon_ids', []],
            ['label_id', []],
            ['label_list', []],
            ['recommend', []],
            ['type', 0],
            ['is_gift', 0],
            ['gift_price', 0],
        ]);
        $this->service->batchSetting($data);
        return app('json')->success(100014);
    }

    /**
     * 商品类型接口
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/9/29
     */
    public function productTypeConfig()
    {
        $productTypeConfig = sys_config('product_type_config');
        foreach ($productTypeConfig as $key => $value) {
            $productTypeConfig[$key] = intval($value);
        }
        return app('json')->success(sys_config('product_type_config'));
    }

    /**
     * 商品迁移导出
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/10/9
     */
    public function productExport()
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['type', 1],
            ['sales', 'normal']
        ]);
        $where['virtual_type'] = 0;
        return app('json')->success($this->service->productExportList($where));
    }

    /**
     * 商品迁移导入
     * @return \think\Response
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/10/9
     */
    public function productImport()
    {
        [$file] = $this->request->getMore([
            ['file', ""]
        ], true);
        if (!$file) return app('json')->fail(400168);
        $res = $this->service->productImport($file);
        return app('json')->success('导入成功', $res);
    }

    /**
     * 回收站商品彻底删除
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/10/9
     */
    public function fullDel($id)
    {
        app()->make(SystemClearData::class)->recycleProduct($id);
        return app('json')->success('删除成功');
    }

    public function otherInfo($id, $type)
    {
        if (!$id) return app('json')->fail('参数错误');
        return app('json')->success($this->service->otherInfo($id, $type));
    }

    public function otherSave($id, $type)
    {
        $data = $this->request->postMore([
            ['is_sub', 0],
            ['is_vip', 0],
            ['vip_product', 0],
            ['attr_value', []],
        ]);
        $this->service->otherSave($id, $type, $data);
        return app('json')->success('保存成功');
    }
}
