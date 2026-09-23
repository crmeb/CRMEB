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
namespace app\adminapi\controller\setting;

use app\adminapi\controller\AuthController;
use app\services\system\lang\LangCodeServices;
use think\facade\App;

class LangCodeController extends AuthController
{
    /**
     * @var LangCodeServices
     */
    protected $services;


    /**
     * @param App $app
     * @param LangCodeServices $services
     */
    public function __construct(App $app, LangCodeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取语言列表
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function langCodeList()
    {
        $where = $this->request->getMore([
            ['is_admin', 0],
            ['type_id', 0],
            ['code', ''],
            ['remarks', '']
        ]);
        return app('json')->success($this->services->langCodeList($where));
    }

    /**
     * 获取语言详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function langCodeInfo()
    {
        [$code] = $this->request->getMore([
            ['code', ''],
        ], true);
        return app('json')->success($this->services->langCodeInfo($code));
    }

    /**
     * 新增编辑语言
     * @return mixed
     * @throws \Exception
     */
    public function langCodeSave()
    {
        $data = $this->request->postMore([
            ['is_admin', 0],
            ['code', ''],
            ['remarks', ''],
            ['edit', 0],
            ['list', []]
        ]);
        $this->services->langCodeSave($data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除语言
     * @param $id
     * @return mixed
     */
    public function langCodeDel($id)
    {
        $this->services->langCodeDel($id);
        return app('json')->success('删除成功');
    }

    /**
     * 机器翻译
     * @return mixed
     * @throws \Throwable
     */
    public function langCodeTranslate()
    {
        [$text] = $this->request->postMore([
            ['text', '']
        ], true);
        if ($text === '') return app('json')->fail('参数错误');
        return app('json')->success($this->services->langCodeTranslate($text));
    }
}
