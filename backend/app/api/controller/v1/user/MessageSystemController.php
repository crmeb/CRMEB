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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\message\MessageSystemServices;


/**
 * 站内信兼容控制器（兼容旧版 message_system 路由，委托给 NoticeServices）
 * Class MessageSystemController
 * @package app\api\controller\v1\user
 */
class MessageSystemController
{
    /**
     * @var MessageSystemServices
     */
    protected $services;

    /**
     * MessageSystemController constructor.
     * @param MessageSystemServices $services
     */
    public function __construct(MessageSystemServices $services)
    {
        $this->services = $services;
    }

    /**
     * 站内信消息列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function messageList(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getMessageSystemList($uid));
    }

    /**
     * 站内信消息详情
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(Request $request, $id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $uid = (int)$request->uid();
        $where['uid'] = $uid;
        $where['id'] = $id;
        return app('json')->success($this->services->getInfo($where));
    }

    /**
     * 消息列表字段编辑/修改
     * @param Request $request
     * @return mixed
     */
    public function editMessage(Request $request)
    {
        $data = $request->getMore([
            ['id', 0],
            ['key', ''],
            ['value', ''],
            ['all', 0]
        ]);
        $all = (int)$data['all'];
        if ($all === 1) {
            $this->services->update(['uid' => $request->uid()], [$data['key'] => $data['value']]);
        } else {
            $this->services->update($data['id'], [$data['key'] => $data['value']]);
        }
        return app('json')->success('设置成功');
    }
}
