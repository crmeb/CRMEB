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
declare (strict_types=1);

namespace app\adminapi\controller\v1\marketing\lottery;

use app\adminapi\controller\AuthController;
use app\services\activity\lottery\LuckLotteryRecordServices;
use think\facade\App;

/**
 * 抽奖中奖记录
 * Class LuckLotteryRecord
 * @package app\controller\admin\v1\marketing\lottery
 */
class LuckLotteryRecord extends AuthController
{

    /**
     * LuckLotteryRecord constructor.
     * @param App $app
     * @param LuckLotteryRecordServices $services
     */
    public function __construct(App $app, LuckLotteryRecordServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 抽奖记录列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->postMore([
            ['is_receive', ''],
            ['is_deliver', ''],
            ['type', ''],
            ['keyword', ''],
            ['data', '', '', 'time'],
            ['factor', ''],
        ]);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 中奖发货
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deliver($id)
    {
        $data = $this->request->postMore([
            ['deliver_name', ''],
            ['deliver_number', ''],
            ['mark', ''],
        ]);
        if (!$id) {
            return app('json')->fail(100100);
        }
        $this->services->setDeliver((int)$id, $data);
        return app('json')->success($this->services->setDeliver((int)$id, $data) ? 100014 : 100015);
    }
}
