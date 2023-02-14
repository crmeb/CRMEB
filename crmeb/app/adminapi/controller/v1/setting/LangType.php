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
namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\services\system\lang\LangTypeServices;
use crmeb\services\CacheService;
use think\facade\App;

class LangType extends AuthController
{
    /**
     * @param App $app
     * @param LangTypeServices $services
     */
    public function __construct(App $app, LangTypeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取语言类型列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function langTypeList()
    {
        $where['is_del'] = 0;
        return app('json')->success($this->services->langTypeList($where));
    }

    /**
     * 添加语言类型表单
     * @param int $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function langTypeForm(int $id = 0)
    {
        return app('json')->success($this->services->langTypeForm($id));
    }

    /**
     * 保存语言类型
     * @return mixed
     */
    public function langTypeSave()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['language_name', ''],
            ['file_name', ''],
            ['is_default', 0],
            ['status', 0]
        ]);
        $this->services->langTypeSave($data);
        CacheService::delete('lang_type_data');
        return app('json')->success(100000);
    }

    /**
     * 修改语言类型状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function langTypeStatus($id, $status)
    {
        $this->services->langTypeStatus($id, $status);
        return app('json')->success(100014);
    }

    /**
     * 删除语言类型
     * @param int $id
     * @return mixed
     */
    public function langTypeDel(int $id = 0)
    {
        $this->services->langTypeDel($id);
        CacheService::delete('lang_type_data');
        return app('json')->success(100002);
    }
}