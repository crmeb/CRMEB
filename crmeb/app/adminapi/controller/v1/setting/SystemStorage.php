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

namespace app\adminapi\controller\v1\setting;


use app\adminapi\controller\AuthController;
use app\services\system\config\SystemConfigServices;
use app\services\system\config\SystemStorageServices;
use crmeb\services\UploadService;
use think\facade\App;

/**
 * Class SystemStorage
 * @package app\adminapi\controller\v1\setting
 */
class SystemStorage extends AuthController
{

    /**
     * SystemStorage constructor.
     * @param App $app
     * @param SystemStorageServices $services
     */
    public function __construct(App $app, SystemStorageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return app('json')->success($this->services->getList(['type' => $this->request->get('type')]));
    }

    /**
     * 获取创建数据表单
     * @param $type
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create($type)
    {
        if (!$type) {
            return app('json')->fail('缺少参数');
        }
        return app('json')->success($this->services->getFormStorage((int)$type));
    }

    /**
     * 获取配置表单
     * @param $type
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getConfigForm($type)
    {
        return app('json')->success($this->services->getFormStorageConfig((int)$type));
    }

    /**
     * 获取配置类型
     * @return mixed
     */
    public function getConfig()
    {
        return app('json')->success(['type' => (int)sys_config('upload_type', 1)]);
    }

    /**
     * @param SystemConfigServices $services
     * @return mixed
     */
    public function saveConfig(SystemConfigServices $services)
    {
        $type = (int)$this->request->post('type', 0);
//        $services->update('upload_type', ['value' => json_encode($type)], 'menu_name');
//        if (1 === $type) {
//            $this->services->transaction(function () {
//                $this->services->update(['status' => 1, 'is_delete' => 0], ['status' => 0]);
//            });
//        }
//        \crmeb\services\CacheService::clear();

        $data = $this->request->postMore([
            ['accessKey', ''],
            ['secretKey', ''],
            ['appid', ''],
        ]);

        $this->services->saveConfig((int)$type, $data);

        return app('json')->success('保存成功');
    }

    /**
     * @param $type
     * @return mixed
     */
    public function synch($type)
    {
        $this->services->synchronization((int)$type);
        return app('json')->success('同步成功');
    }

    /**
     * 保存类型
     * @param $type
     * @return mixed
     */
    public function save($type)
    {
        $data = $this->request->postMore([
            ['accessKey', ''],
            ['secretKey', ''],
            ['appid', ''],
            ['name', ''],
            ['region', ''],
            ['acl', ''],
        ]);
        $type = (int)$type;
        if ($type === 4) {
            if (!$data['appid'] && !sys_config('tengxun_appid')) {
                return app('json')->fail('缺少APPID');
            }
        }
        if (!$data['accessKey']) {
            unset($data['accessKey'], $data['secretKey'], $data['appid']);
        }
        $this->services->saveStorage((int)$type, $data);

        return app('json')->success('添加成功');
    }

    /**
     * 修改状态
     * @param SystemConfigServices $services
     * @param $id
     * @return mixed
     */
    public function status(SystemConfigServices $services, $id)
    {
        if (!$id) {
            return app('json')->fail('缺少参数');
        }

        $info = $this->services->get($id);
        $info->status = 1;
        if (!$info->domain) {
            return app('json')->fail('请先设置空间域名');
        }
//        $services->update('upload_type', ['value' => json_encode($info->type)], 'menu_name');
        \crmeb\services\CacheService::clear();

        //设置跨域规则
        try {
            $upload = UploadService::init($info->type);
            $upload->setBucketCors($info->name, $info->region);
        } catch (\Throwable $e) {
        }

        //修改状态
        $this->services->transaction(function () use ($id, $info) {
//            $this->services->update(['status' => 1, 'is_delete' => 0], ['status' => 0]);
            $this->services->update(['type' => $info->type], ['status' => 0]);
            $info->save();
        });
        return app('json')->success('修改成功');
    }

    /**
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getUpdateDomainForm($id)
    {
        return app('json')->success($this->services->getUpdateDomainForm((int)$id));
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateDomain($id)
    {
        $domain = $this->request->post('domain', '');
        $data = $this->request->postMore([
            ['pri', ''],
            ['ca', '']
        ]);
        if (!$domain) {
            return app('json')->fail('缺少参数');
        }
        if (strstr($domain, 'https://') === false && strstr($domain, 'http://') === false) {
            return app('json')->fail('格式错误，请输入格式为：http://域名');

        }
//        if (strstr($domain, 'https://') !== false && !$data['pri']) {
//            return app('json')->fail('域名为HTTPS访问时，必须填写证书');
//        }

        $this->services->updateDomain($id, $domain);

        return app('json')->success('修改成功');
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail('缺少参数');
        }

        if ($this->services->deleteStorage($id)) {
            return app('json')->success('删除成功');
        } else {
            return app('json')->fail('删除失败');
        }
    }

    /**
     * 切换存储类型
     * @param SystemConfigServices $services
     * @param $type
     * @return mixed
     */
    public function uploadType(SystemConfigServices $services, $type)
    {
        $status = $this->services->count(['type' => $type, 'status' => 1]);
        if (!$status && $type != 1) {
            return app('json')->success('未有正在使用的存储空间');
        }
        $services->update('upload_type', ['value' => json_encode($type)], 'menu_name');
        \crmeb\services\CacheService::clear();
        if ($type != 1) {
            $msg = '切换云存储成功,请检查是否开启使用了存储空间！';
        } else {
            $msg = '切换本地存储成功';
        }
        return app('json')->success($msg);
    }
}
