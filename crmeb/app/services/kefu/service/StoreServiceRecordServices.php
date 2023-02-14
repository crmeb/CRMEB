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

namespace app\services\kefu\service;


use app\dao\service\StoreServiceRecordDao;
use app\services\BaseServices;
use crmeb\utils\Str;
use think\Model;

/**
 * Class StoreServiceRecordServices
 * @package app\services\kefu\service
 * @method array|Model|null getLatelyMsgUid(array $where, string $key) 查询最近和用户聊天的uid用户
 */
class StoreServiceRecordServices extends BaseServices
{

    /**
     * StoreServiceRecordServices constructor.
     * @param StoreServiceRecordDao $dao
     */
    public function __construct(StoreServiceRecordDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取客服用户聊天列表
     * @param int $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceList(int $userId, string $nickname, int $isTourist = 0)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getServiceList(['user_id' => $userId, 'title' => $nickname, 'is_tourist' => $isTourist], $page, $limit, ['user', 'service']);
        foreach ($list as &$item) {
            if ($item['message_type'] == 1) {
                $item['message'] = Str::substrUTf8($item['message'], '10', 'UTF-8', '');
            }
            if (isset($item['kefu_nickname']) && $item['kefu_nickname']) {
                $item['nickname'] = $item['kefu_nickname'];
            }
            if (isset($item['wx_nickname']) && $item['wx_nickname'] && !$item['nickname']) {
                $item['nickname'] = $item['wx_nickname'];
            }
            if (isset($item['kefu_avatar']) && $item['kefu_avatar']) {
                $item['avatar'] = $item['kefu_avatar'];
            }
            if (isset($item['wx_avatar']) && $item['wx_avatar'] && !$item['avatar']) {
                $item['avatar'] = $item['wx_avatar'];
            }
            $item['_update_time'] = date('Y-m-d H:i', $item['update_time']);
        }
        return $list;
    }

    /**
     * 更新客服用户信息
     * @param int $uid
     * @param array $data
     * @return mixed
     */
    public function updateRecord(array $where, array $data)
    {
        return $this->dao->update($where, $data);
    }

    /**
     * 写入聊天相关人数据
     * @param int $uid
     * @param int $toUid
     * @param string $message
     * @param int $type
     * @param int $messageType
     * @param int $num
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveRecord(int $uid, int $toUid, string $message, int $type, int $messageType, int $num, int $isTourist = 0, string $nickname = '', string $avatar = '')
    {
        $info = $this->dao->get(['user_id' => $toUid, 'to_uid' => $uid]);
        if ($info) {
            $info->type = $type;
            $info->message = $message;
            $info->message_type = $messageType;
            $info->update_time = time();
            $info->mssage_num = $num;
            if ($avatar) $info->avatar = $avatar;
            if ($nickname) $info->nickname = $nickname;
            $info->save();
            $this->dao->update(['user_id' => $uid, 'to_uid' => $toUid], ['message' => $message, 'message_type' => $messageType]);
            return $info->toArray();
        } else {
            return $this->dao->save([
                'user_id' => $toUid,
                'to_uid' => $uid,
                'type' => $type,
                'message' => $message,
                'avatar' => $avatar,
                'nickname' => $nickname,
                'message_type' => $messageType,
                'mssage_num' => $num,
                'add_time' => time(),
                'update_time' => time(),
                'is_tourist' => $isTourist
            ])->toArray();
        }
    }
}
