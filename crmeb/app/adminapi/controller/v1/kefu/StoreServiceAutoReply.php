<?php
/**
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/8/3
 */

namespace app\adminapi\controller\v1\kefu;

use app\adminapi\controller\AuthController;
use app\services\wechat\WechatReplyServices;

class StoreServiceAutoReply extends AuthController
{
    /**
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/3
     */
    public function autoReplyList()
    {
        $where = $this->request->getMore([
            ['key', ''],
            ['type', ''],
        ]);
        $where['key_type'] = 1;
        $list = app()->make(WechatReplyServices::class)->getKeyAll($where);
        return app('json')->success($list);
    }

    /**
     * 获取自动回复表单
     * @param int $id
     * @return \think\Response
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/3
     */
    public function autoReplyForm($id = 0)
    {
        return app('json')->success(app()->make(WechatReplyServices::class)->autoReplyForm($id));
    }

    /**
     * 保存自动回复
     * @param int $id
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/3
     */
    public function autoReplySave($id = 0)
    {
        $data = $this->request->postMore([
            ['keys', ''],
            ['type', ''],
            ['data', ''],
            ['status', 1],
        ]);
        app()->make(WechatReplyServices::class)->autoReplySave($id, $data);
        return app('json')->success(100000);
    }

    /**
     * 删除自动回复
     * @param $id
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/3
     */
    public function autoReplyDel($id)
    {
        app()->make(WechatReplyServices::class)->autoReplyDel($id);
        return app('json')->success(100002);
    }
}