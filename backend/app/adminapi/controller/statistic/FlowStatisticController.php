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

namespace app\adminapi\controller\statistic;

use app\adminapi\controller\AuthController;
use app\services\statistic\CapitalFlowServices;
use think\facade\App;

class FlowStatisticController extends AuthController
{
    /**
     * @var CapitalFlowServices
     */
    protected $services;


    /**
     * @param App $app
     * @param CapitalFlowServices $services
     */
    public function __construct(App $app, CapitalFlowServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 资金流水
     * @return mixed
     */
    public function getFlowList()
    {
        $where = $this->request->getMore([
            ['time', ''],
            ['trading_type', 0],
            ['keywords', ''],
            ['ids', ''],
            ['export', 0]
        ]);
        $date = $this->services->getFlowList($where);
        return app('json')->success($date);
    }

    /**
     * 资金流水备注
     * @param $id
     * @return mixed
     */
    public function setMark($id)
    {
        $data = $this->request->postMore([
            ['mark', '']
        ]);
        $this->services->setMark($id, $data);
        return app('json')->success('备注成功');
    }

    /**
     * 账单记录
     * @return mixed
     */
    public function getFlowRecord()
    {
        $where = $this->request->getMore([
            ['type', 'day'],
            ['time', '']
        ]);

        // 当$where['time']不为空，且格式不是2025/12/01-2025/12/15时，重置$where['time']为空
        if ($where['time'] !== '' && !preg_match('/^\d{4}\/\d{2}\/\d{2}-\d{4}\/\d{2}\/\d{2}$/', $where['time'])) {
            $where['time'] = '';
        }

        $data = $this->services->getFlowRecord($where);
        return app('json')->success($data);
    }
}
