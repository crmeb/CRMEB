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
namespace app\adminapi\controller\v1\merchant;

use think\facade\App;
use app\adminapi\controller\AuthController;
use app\services\system\store\SystemStoreServices;

/**
 * 门店管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemStore extends AuthController
{
    /**
     * 构造方法
     * SystemStore constructor.
     * @param App $app
     * @param SystemStoreServices $services
     */
    public function __construct(App $app, SystemStoreServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取门店列表1
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            [['keywords', 's'], ''],
            [['type', 'd'], 0]
        ]);
        return app('json')->success($this->services->getStoreList($where));
    }

    /**
     * 获取门店头部
     * @return mixed
     */
    public function get_header()
    {
        $count = $this->services->getStoreData();
        return app('json')->success(compact('count'));
    }

    /**
     * 门店设置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_info()
    {
        [$id] = $this->request->getMore([
            [['id', 'd'], 0],
        ], true);
        $info = $this->services->getStoreDispose($id);
        return app('json')->success(compact('info'));
    }

    /**
     * 位置选择
     * @return mixed
     */
    public function select_address()
    {
        $key = sys_config('tengxun_map_key');
        if (!$key) return app('json')->fail('请前往设置->系统设置->第三方接口设置 配置腾讯地图KEY');
        return app('json')->success(compact('key'));
    }

    /**
     * 设置单个门店是否显示
     * @param string $is_show
     * @param string $id
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && app('json')->fail('缺少参数');
        $res = $this->services->update((int)$id, ['is_show' => (int)$is_show]);
        if ($res) {
            return app('json')->success($is_show == 1 ? '设置显示成功' : '设置隐藏成功');
        } else {
            return app('json')->fail($is_show == 1 ? '设置显示失败' : '设置隐藏失败');
        }
    }

    /**
     * 保存修改门店信息
     * param int $id
     * */
    public function save($id = 0)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['introduction', ''],
            ['image', ''],
            ['oblong_image', ''],
            ['phone', ''],
            ['address', ''],
            ['detailed_address', ''],
            ['latlng', ''],
            ['day_time', []],
        ]);
        $this->validate($data, \app\adminapi\validate\merchant\SystemStoreValidate::class, 'save');

        $data['address'] = implode(',', $data['address']);
        $data['latlng'] = explode(',', $data['latlng']);
        if (!isset($data['latlng'][0]) || !isset($data['latlng'][1])) {
            return app('json')->fail('请选择门店位置');
        }
        $data['latitude'] = $data['latlng'][0];
        $data['longitude'] = $data['latlng'][1];
        $data['day_time'] = implode(' - ', $data['day_time']);
        unset($data['latlng']);
        if ($data['image'] && strstr($data['image'], 'http') === false) {
            $site_url = sys_config('site_url');
            $data['image'] = $site_url . $data['image'];
        }
        $this->services->saveStore((int)$id, $data);
        return app('json')->success('操作成功!');
    }

    /**
     * 删除恢复门店
     * @param $id
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('数据不存在');
        $storeInfo = $this->services->get($id);
        if (!$storeInfo) {
            return app('json')->fail('数据不存在');
        }
        if ($storeInfo->is_del == 1) {
            $storeInfo->is_del = 0;
            if (!$storeInfo->save())
                return app('json')->fail('恢复失败,请稍候再试!');
            else
                return app('json')->success('恢复门店成功!');
        } else {
            $storeInfo->is_del = 1;
            if (!$storeInfo->save())
                return app('json')->fail('删除失败,请稍候再试!');
            else
                return app('json')->success('删除门店成功!');
        }
    }
}
