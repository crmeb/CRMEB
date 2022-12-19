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
namespace app\adminapi\controller\v1\agent;

use app\adminapi\controller\AuthController;
use app\services\agent\AgentLevelServices;
use app\services\agent\AgentManageServices;
use app\services\user\UserServices;
use think\facade\App;

/**
 * 分销商管理控制器
 * Class AgentManage
 * @package app\adminapi\controller\v1\agent
 */
class AgentManage extends AuthController
{
    /**
     * AgentManage constructor.
     * @param App $app
     * @param AgentManageServices $services
     */
    public function __construct(App $app, AgentManageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 分销管理列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['nickname', ''],
            ['data', ''],
        ]);
        return app('json')->success($this->services->agentSystemPage($where));
    }

    /**
     * 分销头部统计
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_badge()
    {
        $where = $this->request->getMore([
            ['data', ''],
            ['nickname', ''],
        ]);
        return app('json')->success(['res' => $this->services->getSpreadBadge($where)]);
    }

    /**
     * 推广人列表
     * @return mixed
     */
    public function get_stair_list()
    {
        $where = $this->request->getMore([
            ['uid', 0],
            ['data', ''],
            ['nickname', ''],
            ['type', '']
        ]);
        return app('json')->success($this->services->getStairList($where));
    }

    /**
     * 推广人列表头部统计
     * @return mixed
     */
    public function get_stair_badge()
    {
        $where = $this->request->getMore([
            ['uid', ''],
            ['data', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        return app('json')->success(['res' => $this->services->getSairBadge($where)]);
    }

    /**
     * 统计推广订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_stair_order_list()
    {
        $where = $this->request->getMore([
            ['uid', 0],
            ['data', ''],
            ['order_id', ''],
            ['type', ''],
        ]);
        return app('json')->success($this->services->getStairOrderList((int)$where['uid'], $where));
    }

    /**
     * 查看公众号推广二维码
     * @param string $uid
     * @param string $action
     * @return mixed
     */
    public function look_code($uid = '', $action = '')
    {
        if (!$uid || !$action) return app('json')->fail(100100);
        try {
            if (method_exists($this, $action)) {
                $res = $this->$action($uid);
                if ($res)
                    return app('json')->success($res);
                else
                    return app('json')->fail(100016);
            } else
                return app('json')->fail(100029);
        } catch (\Exception $e) {
            return app('json')->fail(400212, ['line' => $e->getLine(), 'messag' => $e->getMessage()]);
        }
    }

    /**
     * 获取公众号二维码
     * @param $uid
     * @return array
     */
    public function wechant_code($uid)
    {
        $qr_code = $this->services->wechatCode((int)$uid);
        if (isset($qr_code['url']))
            return ['code_src' => $qr_code['url']];
        else
            return app('json')->fail(100016);
    }

    /**
     * 查看小程序推广二维码
     * @param string $uid
     */
    public function look_xcx_code($uid = '')
    {
        if (!strlen(trim($uid))) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->lookXcxCode((int)$uid));
    }

    /**
     * 查看H5推广二维码
     * @param string $uid
     * @return mixed|string
     */
    public function look_h5_code($uid = '')
    {
        if (!strlen(trim($uid))) return app('json')->fail(100100);
        return app('json')->success($this->services->lookH5Code((int)$uid));
    }

    /**
     * 解除单个用户的推广权限
     * @param $uid
     * @return mixed
     */
    public function delete_spread($uid)
    {
        if (!$uid) app('json')->fail(100100);
        return app('json')->success($this->services->delSpread((int)$uid) ? 100014 : 100015);
    }

    /**
     * 修改上级推广人
     * @param UserServices $services
     * @return mixed
     */
    public function editSpread(UserServices $services)
    {
        [$uid, $spreadUid] = $this->request->postMore([
            [['uid', 'd'], 0],
            [['spread_uid', 'd'], 0],
        ], true);
        if (!$uid || !$spreadUid) {
            return app('json')->fail(100100);
        }
        if ($uid == $spreadUid) {
            return app('json')->fail(400213);
        }
        $userInfo = $services->get($uid);
        if (!$userInfo) {
            return app('json')->fail(400214);
        }
        if (!$services->count(['uid' => $spreadUid])) {
            return app('json')->fail(400215);
        }
        if ($userInfo->spread_uid == $spreadUid) {
            return app('json')->fail(400216);
        }
        $spreadInfo = $services->get($spreadUid);
        if ($spreadInfo->spread_uid == $uid) {
            return app('json')->fail(400217);
        }
        //之前的上级减少推广人数
        if ($userInfo->spread_uid) {
            $oldSpread = $services->get($userInfo->spread_uid);
            $oldSpread->spread_count = $oldSpread->spread_count - 1;
            $oldSpread->save();
        }
        $spreadInfo->spread_count = $spreadInfo->spread_count + 1;
        $spreadInfo->save();
        $userInfo->spread_uid = $spreadUid;
        $userInfo->spread_time = time();
        $userInfo->division_id = $spreadInfo->division_id;
        $userInfo->agent_id = $spreadInfo->agent_id;
        $userInfo->is_staff = $spreadInfo->is_staff;
        $userInfo->save();
        return app('json')->success(100001);
    }

    /**
     * 取消推广员推广资格
     * @param $uid
     * @return mixed
     */
    public function delete_system_spread($uid)
    {
        if (!$uid) app('json')->fail(100100);
        return app('json')->success($this->services->delSystemSpread((int)$uid) ? 100019 : 100020);
    }

    /**
     * 获取赠送分销等级表单
     * @param AgentLevelServices $services
     * @param $uid
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelForm(AgentLevelServices $services, $uid)
    {
        if (!$uid) app('json')->fail(100100);
        return app('json')->success($services->levelForm((int)$uid));
    }

    /**
     * 赠送分销等级
     * @param AgentLevelServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function giveAgentLevel(AgentLevelServices $services)
    {
        [$uid, $id] = $this->request->postMore([
            [['uid', 'd'], 0],
            [['id', 'd'], 0],
        ], true);
        if (!$uid || !$id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($services->givelevel((int)$uid, (int)$id) ? 400218 : 400219);
    }
}
