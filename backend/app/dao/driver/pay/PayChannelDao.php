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

declare(strict_types=1);

namespace app\dao\driver\pay;

use app\model\driver\pay\PayChannel;
use crmeb\basic\BaseDao;

/**
 * 支付方式数据访问层
 * Class PayChannelDao
 * @package app\dao\driver\pay
 */
class PayChannelDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return PayChannel::class;
    }

    /**
     * 获取所有已启用的支付方式（排除软删除）
     * @return \think\Collection
     */
    public function getAllEnabled()
    {
        return $this->getModel()->where('status', 1)->where('is_del', 0)->order('sort', 'desc')->select();
    }

    /**
     * 根据 channel_key 获取支付方式（排除软删除）
     * @param string $channelKey
     * @return PayChannel|null
     */
    public function getByChannelKey(string $channelKey): ?PayChannel
    {
        return $this->getModel()->where('channel_key', $channelKey)->where('is_del', 0)->find();
    }

    /**
     * 获取所有已启用的支付方式（仅 channel_type = 'payment'，排除好友代付等功能型渠道）
     * @return \think\Collection
     */
    public function getPaymentTypes()
    {
        return $this->getModel()
            ->where('channel_type', 'payment')
            ->where('status', 1)
            ->where('is_del', 0)
            ->order('sort', 'desc')
            ->select();
    }

    /**
     * 根据 ID 获取支付方式（排除软删除）
     * @param int $id
     * @return PayChannel|null
     */
    public function getById(int $id): ?PayChannel
    {
        return $this->getModel()->where('id', $id)->where('is_del', 0)->find();
    }
}
