<?php
declare(strict_types=1);

namespace app\api\controller\v1\message;

use app\Request;
use app\services\message\NoticeServices;

/**
 * 用户端站内信控制器
 */
class NoticeController
{
    /**
     * @var NoticeServices
     */
    protected $noticeService;

    /**
     * @param NoticeServices $noticeService
     */
    public function __construct(NoticeServices $noticeService)
    {
        $this->noticeService = $noticeService;
    }

    /**
     * 获取当前用户的站内信列表
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $uid = (int) $request->uid();
        $type = $request->get('type', '');
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $result = $this->noticeService->getListForUser($uid, $type ?: null, (int) $page, (int) $limit);
        return app('json')->success($result);
    }

    /**
     * 获取当前用户的未读站内信数量
     * @param Request $request
     * @return mixed
     */
    public function unreadCount(Request $request)
    {
        $uid = (int) $request->uid();
        $type = $request->get('type', '');
        $count = $this->noticeService->getUnreadCount($uid, $type ?: null);
        return app('json')->success(['count' => $count]);
    }

    /**
     * 标记站内信为已读
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function read(Request $request, int $id)
    {
        $uid = (int) $request->uid();
        $result = $this->noticeService->markAsRead($id, $uid);
        return app('json')->success($result);
    }

    /**
     * 批量标记站内信为已读
     * @param Request $request
     * @return mixed
     */
    public function batchRead(Request $request)
    {
        $uid = (int) $request->uid();
        $ids = $request->post('ids', []);
        if (empty($ids)) {
            return app('json')->success(['count' => 0]);
        }
        $count = $this->noticeService->batchMarkAsRead($ids, $uid);
        return app('json')->success(['count' => $count]);
    }

    /**
     * 标记当前用户的全部站内信为已读
     * @param Request $request
     * @return mixed
     */
    public function readAll(Request $request)
    {
        $uid = (int) $request->uid();
        $result = $this->noticeService->markAllAsRead($uid);
        return app('json')->success($result);
    }

    /**
     * 删除当前用户的站内信
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function delete(Request $request, int $id)
    {
        $uid = (int) $request->uid();
        $result = $this->noticeService->deleteForUser($id, $uid);
        return app('json')->success($result);
    }

    /**
     * 批量删除当前用户的站内信
     * @param Request $request
     * @return mixed
     */
    public function batchDelete(Request $request)
    {
        $uid = (int) $request->uid();
        $ids = $request->post('ids', []);
        if (empty($ids)) {
            return app('json')->success(['count' => 0]);
        }
        $count = $this->noticeService->batchDeleteForUser($ids, $uid);
        return app('json')->success(['count' => $count]);
    }

    /**
     * 获取当前用户的站内信详情
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function detail(Request $request, int $id)
    {
        $uid = (int) $request->uid();
        $result = $this->noticeService->getDetail($id);
        if (!empty($result)) {
            $this->noticeService->markAsRead($id, $uid);
        }
        return app('json')->success($result);
    }
}
