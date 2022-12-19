<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\file;

use app\adminapi\controller\AuthController;
use app\services\system\attachment\SystemAttachmentCategoryServices;
use think\facade\App;

/**
 * 图片分类管理类
 * Class SystemAttachmentCategory
 * @package app\adminapi\controller\v1\file
 */
class SystemAttachmentCategory extends AuthController
{
    /**
     * @var SystemAttachmentCategoryServices
     */
    protected $service;

    /**
     * @param App $app
     * @param SystemAttachmentCategoryServices $service
     */
    public function __construct(App $app, SystemAttachmentCategoryServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 显示资源列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['pid', 0]
        ]);
        if ($where['name'] != '') $where['pid'] = '';
        return app('json')->success($this->service->getAll($where));
    }

    /**
     * 新增表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create($id)
    {
        return app('json')->success($this->service->createForm($id));
    }

    /**
     * 保存新增
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['name', '']
        ]);
        if (!$data['name']) {
            return app('json')->fail(400100);
        }
        $this->service->save($data);
        return app('json')->success(100021);
    }

    /**
     * 编辑表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return app('json')->success($this->service->editForm($id));
    }

    /**
     * 保存更新的资源
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['name', '']
        ]);
        if (!$data['name']) {
            return app('json')->fail(400100);
        }
        $info = $this->service->get($id);
        $count = $this->service->count(['pid' => $id]);
        if ($count && $info['pid'] != $data['pid']) return app('json')->fail(400105);
        $this->service->update($id, $data);
        return app('json')->success(100001);
    }

    /**
     * 删除指定资源
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->service->del($id);
        return app('json')->success(100002);
    }
}
