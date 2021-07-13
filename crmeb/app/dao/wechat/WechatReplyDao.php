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

namespace app\dao\wechat;

use think\model;
use app\dao\BaseDao;
use app\model\wechat\WechatReply;

/**
 *
 * Class UserWechatUserDao
 * @package app\dao\user
 */
class WechatReplyDao extends BaseDao
{
    /**
     * @return string
     */
    protected function setModel(): string
    {
        return WechatReply::class;
    }

    /**
     * 获取关键字
     * @param $key
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getKey($key)
    {
        $res = $this->getModel()->whereIn('id', function ($query) use ($key) {
            $query->name('wechat_key')->where('keys', $key)->field(['reply_id'])->select();
        })->where('status', '1')->find();
        if (empty($res)) {
            $res = $this->getModel()->whereIn('id', function ($query) use ($key) {
                $query->name('wechat_key')->where('keys', 'default')->field(['reply_id'])->select();
            })->where('status', '1')->find();
        }
        return $res;
    }

}
