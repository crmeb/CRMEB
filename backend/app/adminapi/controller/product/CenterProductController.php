<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\product;


use app\adminapi\controller\AuthController;
use app\services\order\StoreCartServices;
use app\services\other\CacheServices;
use app\services\product\CenterProductServices;
use crmeb\trade\product\ProductTypeRegistry;
use crmeb\utils\File;
use app\services\driver\upload\UploadService;
use think\facade\App;
use think\Request;

/**
 * 中台商品统一管理控制器
 * 由旧 StoreProductController 迁移而来，统一走中台入口
 */
class CenterProductController extends AuthController
{
    protected $services;

    public function __construct(App $app, CenterProductServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表头部
     * @return mixed
     */
    public function typeHeader()
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
        $list = $this->services->getHeader($where);
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
            ['is_sub', 0],//佣金是单独还是默认
            ['is_vip',0],
            ['sort', 0],
            ['sales', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['temp_id', 0],
            // is_hot/is_benefit/is_best/is_new/is_good 推荐位字段已删除
            ['mer_use', 0],
            ['is_postage', 0],
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
        return app('json')->success('保存成功');
    }

    /**
     * 删除数据缓存
     * @param CacheServices $services
     * @return mixed
     */
    public function deleteCacheData(CacheServices $services)
    {
        $services->delectDbCache($this->adminId . '_product_data');
        return app('json')->success('删除成功');
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
            ['product_type', ''],
        ]);
        $data = $this->services->getList($where);
        return app('json')->success($data);
    }

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     * @return mixed
     */
    public function setShow($is_show = '', $id = '')
    {
        $del = $this->services->value(['id' => $id], 'is_del');
        if ($del == 1) return app('json')->fail('商品已删除，请先恢复商品');
        $this->services->setShow([$id], $is_show);
        return app('json')->success('设置成功');
    }

    /**
     * 设置批量商品上架
     * @return mixed
     */
    public function productShow()
    {
        [$ids] = $this->request->postMore([
            ['ids', []]
        ], true);
        $this->services->setShow($ids, 1);
        return app('json')->success('设置成功');
    }

    /**
     * 设置批量商品下架
     * @return mixed
     */
    public function productUnshow()
    {
        [$ids] = $this->request->postMore([
            ['ids', []]
        ], true);
        $this->services->setShow($ids, 0);
        return app('json')->success('设置成功');
    }

    /**
     * 获取规格模板
     * @return mixed
     */
    public function getRule()
    {
        $list = $this->services->getRule();
        return app('json')->success($list);
    }

    /**
     * 获取商品详细信息
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductInfo($id = 0)
    {
        return app('json')->success($this->services->getInfo((int)$id));
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
            ['product_type', 'normal'],// 商品类型
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
            ['vip_product', 0],//会员专属（0不限制/1仅会员可见可买/2所有人可见仅会员可买）
            ['is_sub', 0],//佣金是单独还是默认
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
        $this->services->save((int)$id, $data);
        return app('json')->success('保存成功');
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
        $this->services->checkActivity($id);
        $res = $this->services->del($id);
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
        return app('json')->success($this->services->batchDelete($ids));
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
        $this->services->batchRecover($ids);
        return app('json')->success('恢复成功');
    }

    /**
     * 生成规格列表
     * @param int $id
     * @param int $type
     * @return mixed
     */
    public function isFormatAttr($id = 0, $type = 0)
    {
        $data = $this->request->postMore([
            ['attrs', []],
            ['items', []],
            ['is_virtual', 0],
            ['product_type', 'normal']
        ]);
        $info = $this->services->getAttr($data, $id, $type);
        return app('json')->success(compact('info'));
    }


    /**
     * 获取选择的商品列表
     * @return mixed
     */
    public function searchList()
    {
        $where = $this->request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 1],
            ['is_show', -1],
        ]);
        $where['is_del'] = 0;
        if ($where['cate_id'] !== '') {
            $where['cate_id'] = toIntArray($where['cate_id']);
        }
        $list = $this->services->searchList($where);
        return app('json')->success($list);
    }

    /**
     * 获取某个商品规格
     * @return mixed
     */
    public function getAttrs()
    {
        [$id, $type] = $this->request->getMore([
            [['id', 'd'], 0],
            [['type', 'd'], 0],
        ], true);
        $info = $this->services->getProductRules($id, $type);
        return app('json')->success(compact('info'));
    }

    /**
     * 获取运费模板列表
     * @return mixed
     */
    public function getTemplate()
    {
        return app('json')->success($this->services->getTemp());
    }

    /**
     * 获取视频上传token
     * @return mixed
     * @throws \Exception
     */
    public function getTempKeys(Request $request)
    {
        $upload = UploadService::init();
        $type = upload_type();
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
        return $re ? app('json')->success($re) : app('json')->fail('获取失败');
    }

    /**
     * 检测商品是否开活动
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkActivity($id)
    {
        $this->services->checkActivity($id);
        return app('json')->success('检测成功');
    }

    /**
     * 导入卡密
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function importCard()
    {
        $data = $this->request->getMore([
            ['file', ""]
        ]);
        if (!$data['file']) return app('json')->fail('请上传文件');
        $file = public_path() . substr($data['file'], 1);
        // 获取文件后缀
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($suffix, ['xls', 'xlsx'])) {
            return app('json')->fail('文件格式不正确，请上传xls或xlsx格式的文件！');
        }
        /** @var File $readExcelService */
        $readExcelService = app()->make(File::class);
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
        $this->services->batchSetting($data);
        return app('json')->success('设置成功');
    }

    /**
     * 商品类型接口
     * 商品类型配置（product_type_config）已废弃删除，商品类型由产品类型注册中心管理。
     * 旧商品添加页基于数字类型 id 过滤，返回空数组等价于全类型可用，兼容旧前端。
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/9/29
     */
    public function productTypeConfig()
    {
        return app('json')->success([]);
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
            ['sales', 'normal'],
            ['ids', []]
        ]);
        // 默认只导出实物商品，与 product_type 默认类型一致
        $where['product_type'] = 'normal';
        return app('json')->success($this->services->productExportList($where));
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
        if (!$file) return app('json')->fail('请上传文件');
        $res = $this->services->productImport($file);
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
        $this->services->recycleProduct($id);
        return app('json')->success('删除成功');
    }

    /**
     * 产品类型列表（中台路由）
     * 商品类型配置（product_type_config）已废弃删除，改为从产品类型注册中心读取已注册类型。
     * @return \think\Response
     */
    public function typeList()
    {
        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        return app('json')->success($registry->allInfo());
    }

    /**
     * 统一商品列表（中台路由）
     * 页面契约：keyword 搜索、product_type、is_show 上架状态、is_del 数据状态（'' 均表示全部）
     * @return \think\Response
     */
    public function list()
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['keyword', ''],
            ['cate_id', ''],
            ['is_show', ''],
            ['is_del', ''],
            ['type', ''],
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
            ['product_type', ''],
        ]);
        $data = $this->services->getList($where);
        return app('json')->success($data);
    }

    /**
     * 商品搜索（中台路由）
     * @return \think\Response
     */
    public function search()
    {
        $where = $this->request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 1],
            ['is_show', -1],
        ]);
        $where['is_del'] = 0;
        if ($where['cate_id'] !== '') {
            $where['cate_id'] = toIntArray($where['cate_id']);
        }
        $list = $this->services->searchList($where);
        return app('json')->success($list);
    }

    /**
     * 商品详情（中台路由）
     * @param int $id
     * @return \think\Response
     */
    public function detail($id = 0)
    {
        return app('json')->success($this->services->getInfo((int)$id));
    }

    public function otherInfo($id, $type)
    {
        if (!$id) return app('json')->fail('参数错误');
        return app('json')->success($this->services->otherInfo($id, $type));
    }

    public function otherSave($id, $type)
    {
        $data = $this->request->postMore([
            ['is_sub', 0],
            ['is_vip', 0],
            ['vip_product', 0],//会员专属（0不限制/1仅会员可见可买/2所有人可见仅会员可买）
            ['attr_value', []],
        ]);
        $this->services->otherSave($id, $type, $data);
        return app('json')->success('保存成功');
    }
}
