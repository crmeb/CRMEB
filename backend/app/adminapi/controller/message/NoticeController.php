<?php
declare(strict_types=1);

namespace app\adminapi\controller\message;

use app\adminapi\controller\AuthController;
use app\services\message\NoticeServices;
use think\facade\App;

/**
 * 站内信管理控制器
 */
class NoticeController extends AuthController
{
    /**
     * @var NoticeServices
     */
    protected $noticeService;

    /**
     * @param App $app
     * @param NoticeServices $noticeService
     */
    public function __construct(App $app, NoticeServices $noticeService)
    {
        parent::__construct($app);
        $this->noticeService = $noticeService;
    }

    /**
     * 获取站内信列表
     * @return mixed
     */
    public function index()
    {
        $filter = [
            'type' => $this->request->get('type', ''),
            'status' => $this->request->get('status', ''),
            'keyword' => $this->request->get('keyword', ''),
        ];
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        $result = $this->noticeService->getListForAdmin($filter, (int) $page, (int) $limit);
        return app('json')->success($result);
    }

    /**
     * 创建站内信
     * @return mixed
     */
    public function create()
    {
        $data = $this->request->post();
        $this->validate($data, [
            'type' => 'require',
            'title' => 'require|max:255',
            'content' => 'require',
            'target_type' => 'require|in:all,user,level',
        ]);
        $data['created_by'] = (int) $this->adminId;
        $result = $this->noticeService->create($data);
        return app('json')->success($result);
    }

    /**
     * 更新站内信
     * @param int $id
     * @return mixed
     */
    public function update(int $id)
    {
        $data = $this->request->post();
        $this->validate($data, [
            'type'        => 'require',
            'title'       => 'require|max:255',
            'content'     => 'require',
            'target_type' => 'require|in:all,user,level',
        ]);
        $result = $this->noticeService->update($id, $data);
        return app('json')->success($result);
    }

    /**
     * 删除站内信
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        $result = $this->noticeService->delete($id);
        return app('json')->success($result);
    }

    /**
     * 发布站内信
     * @param int $id
     * @return mixed
     */
    public function publish(int $id)
    {
        $result = $this->noticeService->publish($id);
        return app('json')->success($result);
    }

    /**
     * 撤回站内信
     * @param int $id
     * @return mixed
     */
    public function revoke(int $id)
    {
        $result = $this->noticeService->revoke($id);
        return app('json')->success($result);
    }

    /**
     * 获取站内信详情
     * @param int $id
     * @return mixed
     */
    public function detail(int $id)
    {
        $result = $this->noticeService->getDetail($id);
        return app('json')->success($result);
    }

    /**
     * 发送系统通知
     * @return mixed
     */
    public function sendSystemNotice()
    {
        $data = $this->request->post();
        $this->validate($data, [
            'title' => 'require|max:255',
            'content' => 'require',
        ]);
        $data['created_by'] = (int) $this->adminId;
        $result = $this->noticeService->sendSystemNotice(
            $data['title'],
            $data['content'],
            $data
        );
        return app('json')->success($result);
    }
}
