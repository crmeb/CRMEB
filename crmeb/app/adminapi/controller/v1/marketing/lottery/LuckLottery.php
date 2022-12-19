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
use app\services\activity\lottery\LuckLotteryServices;
use think\facade\App;

/**
 * 抽奖活动
 * Class LuckLottery
 * @package app\controller\admin\v1\marketing\lottery
 */
class LuckLottery extends AuthController
{

    /**
     * LuckLottery constructor.
     * @param App $app
     * @param LuckLotteryServices $services
     */
    public function __construct(App $app, LuckLotteryServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 抽奖列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->postMore([
            ['start_status', '', '', 'start'],
            ['status', ''],
            ['factor', ''],
            ['store_name', '', '', 'keyword'],
        ]);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 抽奖活动详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->getlotteryInfo((int)$id));
    }

    /**
     * 根据类型获取详情
     * @param $factor
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function factorInfo($factor)
    {
        if (!$factor) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->getlotteryFactorInfo((int)$factor));
    }

    /**
     * 添加抽奖
     * @return mixed
     */
    public function add()
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['desc', ''],
            ['image', ''],
            ['factor', 1],
            ['factor_num', 1],
            ['attends_user', 1],
            ['user_level', []],
            ['user_label', []],
            ['is_svip', 0],
            ['period', [0, 0]],
            ['lottery_num_term', 1],
            ['lottery_num', 1],
            ['spread_num', 1],
            ['is_all_record', 1],
            ['is_personal_record', 1],
            ['is_content', 1],
            ['content', ''],
            ['status', 1],
            ['prize', []]
        ]);
        if (!$data['name']) {
            return app('json')->fail(400501);
        }
        if ($data['is_content'] && !$data['content']) {
            return app('json')->fail(400502);
        }
        [$start, $end] = $data['period'];
        unset($data['period']);
        $data['start_time'] = $start ? strtotime($start) : 0;
        $data['end_time'] = $end ? strtotime($end) : 0;
        if ($data['start_time'] && $data['end_time'] && $data['end_time'] <= $data['start_time']) {
            return app('json')->fail(400503);
        }
        if (!$data['prize']) {
            return app('json')->fail(400504);
        }
        if (in_array($data['factor'], [1, 2]) && !$data['factor_num']) {
            return app('json')->fail(400505);
        }
        return app('json')->success($this->services->add($data) ? 100000 : 100006);
    }

    /**
     * 修改抽奖
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['desc', ''],
            ['image', ''],
            ['factor', 1],
            ['factor_num', 1],
            ['attends_user', 1],
            ['user_level', []],
            ['user_label', []],
            ['is_svip', 0],
            ['period', [0, 0]],
            ['lottery_num_term', 1],
            ['lottery_num', 1],
            ['spread_num', 1],
            ['is_all_record', 1],
            ['is_personal_record', 1],
            ['is_content', 1],
            ['content', ''],
            ['status', 1],
            ['prize', []]
        ]);
        if (!$id) {
            return app('json')->fail(100100);
        }
        if (!$data['name']) {
            return app('json')->fail(400501);
        }
        [$start, $end] = $data['period'];
        unset($data['period']);
        $data['start_time'] = $start ? strtotime($start) : 0;
        $data['end_time'] = $end ? strtotime($end) : 0;
        if ($data['start_time'] && $data['end_time'] && $data['end_time'] <= $data['start_time']) {
            return app('json')->fail(400503);
        }
        if ($data['is_content'] && !$data['content']) {
            return app('json')->fail(400502);
        }
        if (!$data['prize']) {
            return app('json')->fail(400504);
        }
        if (in_array($data['factor'], [1, 2]) && !$data['factor_num']) {
            return app('json')->fail(400505);
        }
        return app('json')->success($this->services->edit((int)$id, $data) ? 100001 : 100007);
    }

    /**
     * 删除抽奖
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete()
    {
        list($id) = $this->request->getMore([
            ['id', 0],
        ], true);
        if (!$id) return app('json')->fail(100026);
        $this->services->delLottery((int)$id);
        return app('json')->success(100002);
    }

    /**
     * 设置活动状态
     * @param string $id
     * @param string $status
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setStatus($id = '', $status = '')
    {
        if ($status == '' || $id == '') return app('json')->fail(100100);
        $this->services->setStatus((int)$id, (int)$status);
        return app('json')->success(100014);
    }
}
