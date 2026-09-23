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
/**
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/31
 */

namespace modules\marketing\signin\controller\admin;

use app\adminapi\controller\AuthController;
use modules\marketing\signin\service\SystemSignRewardServices;
use think\facade\App;

class SignRewardsController extends AuthController
{
    /**
     * @var SystemSignRewardServices
     */
    protected $services;


    /**
     * @param App $app
     * @param SystemSignRewardServices $services
     */
    public function __construct(App $app, SystemSignRewardServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 签到奖励列表
     * @return \think\Response
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function index()
    {
        [$type] = $this->request->getMore([
            ['type', 0]
        ], true);
        $data = $this->services->getList($type);
        return app('json')->success($data);
    }

    /**
     * 新增签到奖励
     * @return \think\Response
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function addRewards()
    {
        [$type] = $this->request->getMore([
            ['type', 0]
        ], true);
        $data = $this->services->rewardsForm(0, $type);
        return app('json')->success($data);
    }

    /**
     * 修改签到奖励
     * @param $id
     * @return \think\Response
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function editRewards($id)
    {
        $data = $this->services->rewardsForm($id);
        return app('json')->success($data);
    }

    /**
     * 保存签到奖励
     * @param $id
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function saveRewards($id)
    {
        $data = $this->request->postMore([
            ['type', 0],
            ['days', 0],
            ['point', 0],
            ['exp', 0]
        ]);
        $this->services->saveRewards($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除签到奖励
     * @param $id
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function delRewards($id)
    {
        $this->services->delete($id);
        return app('json')->success('删除成功');
    }
}