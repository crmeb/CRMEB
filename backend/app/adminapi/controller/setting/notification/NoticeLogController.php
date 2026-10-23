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

namespace app\adminapi\controller\setting\notification;

use app\adminapi\controller\AuthController;
use app\services\message\admin\NoticeLogServices;
use think\facade\App;

class NoticeLogController extends AuthController
{
    protected $services;

    public function __construct(App $app, NoticeLogServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function index()
    {
        $filter = $this->request->getMore([
            ['mark', ''],
            ['uid', ''],
            ['channel_key', ''],
            ['status', ''],
            ['start_time', ''],
            ['end_time', ''],
        ]);
        $page = max(1, (int)$this->request->get('page', 1));
        $limit = (int)$this->request->get('limit', 20);
        $limit = $limit > 0 && $limit <= 100 ? $limit : 20;

        return app('json')->success($this->services->getList($filter, $page, $limit));
    }

    public function detail(int $id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $info = $this->services->getDetail($id);
        if (empty($info)) {
            return app('json')->fail('记录不存在');
        }
        return app('json')->success($info);
    }

    public function statistics()
    {
        // 与列表保持同一套筛选条件（状态筛选不参与统计）
        $filter = $this->request->getMore([
            ['mark', ''],
            ['uid', ''],
            ['channel_key', ''],
            ['start_time', ''],
            ['end_time', ''],
        ]);

        return app('json')->success($this->services->getStatistics($filter));
    }

    public function retry(int $id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $info = $this->services->getDetail($id);
        if (empty($info)) {
            return app('json')->fail('记录不存在');
        }

        // 日志只有渲染后的内容，没有原始变量；重新执行会发送残缺信息。
        return app('json')->fail('该日志未保存原始变量，无法安全重试');
    }

    public function clean()
    {
        $days = $this->request->post('days', 30);
        $count = $this->services->cleanLogs((int)$days);
        return app('json')->success('清理成功，共删除 ' . $count . ' 条记录');
    }
}
