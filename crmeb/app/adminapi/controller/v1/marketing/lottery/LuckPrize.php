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
declare (strict_types=1);

namespace app\adminapi\controller\v1\marketing\lottery;

use app\adminapi\controller\AuthController;
use app\services\activity\lottery\LuckPrizeServices;
use think\facade\App;

/**
 * 抽奖奖品
 * Class LuckPrize
 * @package app\controller\admin\v1\marketing\lottery
 */
class LuckPrize extends AuthController
{

    /**
     * LuckPrize constructor.
     * @param App $app
     * @param LuckPrizeServices $services
     */
    public function __construct(App $app, LuckPrizeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function edit($id)
    {
        $data = $this->request->postMore([
            ['type', 1],
            ['lottery_id', 0],
            ['name', ''],
            ['prompt', ''],
            ['image', ''],
            ['chance', 1],
            ['total', 1],
            ['couon_id', 0],
            ['product_id', 0],
            ['unique', ''],
            ['num', 1]
        ]);
        if (!$id) {
            return app('json')->fail('缺少参数id');
        }
        if (!$data['lottery_id']) {
            return app('json')->fail('缺少抽奖活动id');
        }
        return app('json')->success($this->services->edit((int)$id, $data) ? '编辑成功' : '编辑失败');
    }

}
