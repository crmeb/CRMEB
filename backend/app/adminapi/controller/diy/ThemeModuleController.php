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
namespace app\adminapi\controller\diy;

use app\adminapi\controller\AuthController;
use app\services\diy\ThemeModuleServices;
use crmeb\exceptions\AdminException;
use think\facade\App;

/**
 * 主题组件管理
 */
class ThemeModuleController extends AuthController
{
    /**
     * @var ThemeModuleServices
     */
    protected $services;

    /**
     * 构造方法
     * @param App $app
     * @param ThemeModuleServices $services
     */
    public function __construct(App $app, ThemeModuleServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 组件列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['type', ''],
        ]);
        $data = $this->services->getModuleList($where);
        return app('json')->success($data);
    }

    /**
     * 组件详情
     * @param int $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read(int $id)
    {
        if (!$id) {
            throw new AdminException('参数错误');
        }
        $info = $this->services->getModuleInfo($id);
        return app('json')->success($info);
    }

    /**
     * 新增组件
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['type', ''],
            ['data', ''],
        ]);
        $id = $this->services->saveModule(0, $data);
        return app('json')->success('保存成功', ['id' => $id]);
    }

    /**
     * 编辑组件
     * @param int $id
     * @return \think\Response
     */
    public function update(int $id)
    {
        if (!$id) {
            throw new AdminException('参数错误');
        }
        $data = $this->request->postMore([
            ['type', ''],
            ['data', ''],
        ]);
        $this->services->saveModule($id, $data);
        return app('json')->success('保存成功', ['id' => $id]);
    }

    /**
     * 删除组件
     * @param int $id
     * @return \think\Response
     */
    public function delete(int $id)
    {
        if (!$id) {
            throw new AdminException('参数错误');
        }
        $this->services->deleteModule($id);
        return app('json')->success('删除成功');
    }
}

