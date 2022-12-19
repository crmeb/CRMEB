<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\services\other\AgreementServices;
use think\facade\App;

class SystemAgreement extends AuthController
{
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
        if (!$type) return app('json')->fail(400184);
        $info = $this->services->getAgreementBytype($type);
        return app('json')->success($info);
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
        ]);
        $data['status'] = 1;
        $this->services->saveAgreement($data, $data['id']);
        return app('json')->success(100000);
    }
}
