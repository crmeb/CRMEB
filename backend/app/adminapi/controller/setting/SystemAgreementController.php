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
use app\services\other\AgreementServices;
use think\facade\App;

class SystemAgreementController extends AuthController
{
    /**
     * @var AgreementServices
     */
    protected $services;


    /**
     * 构造方法
     * SystemCity constructor.
     * @param App $app
     * @param AgreementServices $services
     */
    public function __construct(App $app, AgreementServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取协议内容
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgreement($type)
    {
        if (!$type) return app('json')->fail('协议类型不存在');
        $info = $this->services->getAgreementBytype($type);
        return app('json')->success($info);
    }

    /**
     * 协议列表（系统协议 + 已安装插件协议），含 AI 提示词，供协议设置页动态渲染
     * @return mixed
     */
    public function agreementList()
    {
        return app('json')->success($this->services->getAgreementList());
    }

    /**
     * 保存协议内容
     * @return mixed
     */
    public function saveAgreement()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['type', 0],
            ['title', ''],
            ['content', ''],
            ['system_prompt', ''],
            ['user_prompt', ''],
        ]);
        $data['status'] = 1;
        $this->services->saveAgreement($data, $data['id']);
        return app('json')->success('保存成功');
    }
}
