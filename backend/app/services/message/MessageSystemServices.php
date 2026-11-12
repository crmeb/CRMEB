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
declare (strict_types=1);

namespace app\services\message;

use app\dao\system\MessageSystemDao;
use app\services\message\NoticeServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\ApiException;

/**
 * 站内信兼容服务（Phase 2：内部委托给 NoticeServices / NoticeReadDao）
 * 保持原有接口签名不变，底层改走 eb_notice + eb_notice_read
 *
 * @method save(array $data)                    已废弃，Phase 2 后 SystemChannel 走 NoticeServices
 * @method saveAll(array $data)                 已废弃，Phase 2 后 kefuSystemSend 走 NoticeServices
 * @method update($id, array $data, ?string $key = null) 重写，见下方 override
 * @method count(array $where)                  已废弃，改用 NoticeServices::getUnreadCount()
 */
class MessageSystemServices extends BaseService
{
    /**
     * @var MessageSystemDao
     */
    protected $dao;

    /**
     * MessageSystemServices constructor.
     * @param MessageSystemDao $dao
     */
    public function __construct(MessageSystemDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 站内信列表（兼容层：委托 NoticeServices）
     * @param int $uid
     * @return array
     */
    public function getMessageSystemList(int $uid): array
    {
        [$page, $limit] = $this->getPageValue();
        /** @var NoticeServices $noticeService */
        $noticeService = app()->make(NoticeServices::class);
        $result = $noticeService->getListForUser($uid, null, $page, $limit);
        // 旧版消息中心页面依赖 look/add_time 字段渲染已读状态与时间，此处做映射
        $list = array_map(static function (array $item): array {
            $item['look'] = (int)($item['is_read'] ?? 0);
            $item['add_time'] = (int)($item['send_at'] ?? 0) > 0 ? (string)(time_tran((int)$item['send_at']) ?: '刚刚') : '';
            return $item;
        }, $result['list']);
        return ['list' => $list, 'count' => $result['total']];
    }

    /**
     * 站内信详情（兼容层：委托 NoticeServices，自动标记已读）
     * @param array $where
     * @return array
     */
    public function getInfo(array $where): array
    {
        $id = (int)($where['id'] ?? 0);
        $uid = (int)($where['uid'] ?? 0);
        if (!$id) throw new ApiException('参数错误');

        /** @var NoticeServices $noticeService */
        $noticeService = app()->make(NoticeServices::class);
        $info = $noticeService->getDetail($id);
        if (!$info) throw new ApiException('数据不存在');

        // 标记已读
        $noticeService->markAsRead($id, $uid);

        return [
            'id'       => $info['id'],
            'uid'      => $uid,
            'title'    => $info['title'] ?? '',
            'content'  => $info['content'] ?? '',
            'mark'     => $info['module_key'] ?? '',
            'data'     => $info['extra_data'] ?? '',
            'is_del'   => 0,
            'look'     => 1,
            'add_time' => isset($info['send_at']) ? time_tran($info['send_at']) : '',
        ];
    }

    /**
     * 覆盖 BaseService 的 update：拦截 mark 已读 / 删除操作，转发到 notice_read
     * @param int|array $id
     * @param array     $data
     * @param ?string   $key
     * @return mixed
     */
    public function update($id, array $data, ?string $key = null)
    {
        /** @var NoticeServices $noticeService */
        $noticeService = app()->make(NoticeServices::class);

        // 全部已读：update(['uid' => uid], ['look' => 1])
        if (is_array($id) && isset($data['look']) && isset($id['uid'])) {
            return $noticeService->markAllAsRead((int)$id['uid']);
        }

        // 单条更新必须走 updateForUser，禁止按 notice_id 反查他人 uid（防 IDOR）
        throw new ApiException('站内信仅支持标记已读或删除操作');
    }

    /**
     * 当前用户对指定站内信标记已读/删除
     * @param int $uid 当前登录用户
     * @param int $noticeId 通知 ID
     * @param array $data 含 look 或 is_del
     * @return mixed
     */
    public function updateForUser(int $uid, int $noticeId, array $data)
    {
        if ($uid <= 0 || $noticeId <= 0) {
            throw new ApiException('参数错误');
        }
        /** @var NoticeServices $noticeService */
        $noticeService = app()->make(NoticeServices::class);
        if (isset($data['look'])) {
            return $noticeService->markAsRead($noticeId, $uid);
        }
        if (isset($data['is_del'])) {
            return $noticeService->deleteForUser($noticeId, $uid);
        }
        throw new ApiException('站内信仅支持标记已读或删除操作');
    }
}
