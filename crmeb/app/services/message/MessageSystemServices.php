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

namespace app\services\message;

use app\dao\system\MessageSystemDao;
use app\services\BaseServices;
use crmeb\exceptions\ApiException;

/**
 * 站内信services类
 * Class MessageSystemServices
 * @package app\services\system
 * @method save(array $data) 保存数据
 * @method mixed saveAll(array $data) 批量保存数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 *
 */
class MessageSystemServices extends BaseServices
{

    /**
     * SystemNotificationServices constructor.
     * @param MessageSystemDao $dao
     */
    public function __construct(MessageSystemDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 站内信列表
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMessageSystemList($uid)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $where['uid'] = $uid;
        $list = $this->dao->getMessageList($where, '*', $page, $limit);
        $count = $this->dao->getCount($where);
        if (!$list) return ['list' => [], 'count' => 0];
        foreach ($list as &$item) {
            $item['add_time'] = time_tran($item['add_time']);
            if ($item['data'] != '') {
                $item['content'] = getLang($this->getMsgCode($item['mark']), json_decode($item['data'], true));
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 站内信详情
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo($where)
    {
        $info = $this->dao->getOne($where);
        if (!$info || $info['is_del'] == 1) {
            throw new ApiException(100026);
        }
        $info = $info->toArray();
        if ($info['look'] == 0) {
            $this->update($info['id'], ['look' => 1]);
        }
        if ($info['data'] != '') {
            $info['content'] = getLang($this->getMsgCode($info['mark']), json_decode($info['data'], true));
        }
        $info['add_time'] = time_tran($info['add_time']);
        return $info;
    }

    public function getMsgCode($mark)
    {
        switch ($mark) {
            case 'admin_pay_success_code':
                $code = 500004;
                break;
            case 'bind_spread_uid':
                $code = 500005;
                break;
            case 'order_pay_success':
                $code = 500006;
                break;
            case 'order_take':
                $code = 500007;
                break;
            case 'price_revision':
                $code = 500008;
                break;
            case 'order_refund':
                $code = 500009;
                break;
            case 'recharge_success':
                $code = 500010;
                break;
            case 'integral_accout':
                $code = 500011;
                break;
            case 'order_brokerage':
                $code = 500012;
                break;
            case 'bargain_success':
                $code = 500013;
                break;
            case 'order_user_groups_success':
                $code = 500014;
                break;
            case 'send_order_pink_fial':
                $code = 500015;
                break;
            case 'open_pink_success':
                $code = 500016;
                break;
            case 'user_extract':
                $code = 500017;
                break;
            case 'user_balance_change':
                $code = 500018;
                break;
            case 'recharge_order_refund_status':
                $code = 500019;
                break;
            case 'send_order_refund_no_status':
                $code = 500020;
                break;
            case 'send_order_apply_refund':
                $code = 500021;
                break;
            case 'order_deliver_success':
            case 'order_postage_success':
                $code = 500022;
                break;
            case 'send_order_pink_clone':
                $code = 500023;
                break;
            case 'can_pink_success':
                $code = 500024;
                break;
            case 'kefu_send_extract_application':
                $code = 500025;
                break;
            case 'send_admin_confirm_take_over':
                $code = 500026;
                break;
            case 'order_pay_false':
                $code = 500027;
                break;
        }
        return $code;
    }
}
