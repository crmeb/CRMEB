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
use app\services\message\admin\NoticeChannelServices;
use think\facade\App;

class NoticeChannelController extends AuthController
{
    protected $services;

    public function __construct(App $app, NoticeChannelServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function index()
    {
        $filter = $this->request->getMore([
            ['status', ''],
            ['channel_name', ''],
            ['channel_key', ''],
        ]);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);

        return app('json')->success($this->services->getList($filter, (int)$page, (int)$limit));
    }

    public function detail(int $id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $info = $this->services->getDetail($id);
        if (empty($info)) {
            return app('json')->fail('渠道不存在');
        }
        return app('json')->success($info);
    }

    public function update(int $id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $data = $this->request->postMore([
            ['channel_name', ''],
            ['icon', ''],
            ['status', 1],
            ['config', []],
            ['sort', 0],
            ['description', ''],
            ['support_user', 1],
            ['support_platform', 1],
        ]);

        if ($this->services->update($id, $data)) {
            return app('json')->success('更新成功');
        }
        return app('json')->fail('更新失败');
    }

    public function setStatus(int $id, int $status)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        if ($this->services->updateStatus($id, $status)) {
            return app('json')->success('设置成功');
        }
        return app('json')->fail('设置失败');
    }

    public function options()
    {
        // type: 1=用户端 2=平台端 0=全部
        $type = (int)$this->request->get('type', 0);
        return app('json')->success($this->services->getChannelList($type));
    }

    public function test(int $id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $channel = $this->services->getDetail($id);
        if (empty($channel)) {
            return app('json')->fail('渠道不存在');
        }

        try {
            if ((int)$channel['status'] !== 1) return app('json')->fail('渠道全局开关未开启');
            $channelKey = $channel['channel_key'];
            $uid = (int)$this->request->post('uid', 0);
            if ((int)$channel['support_user'] === 1 && $uid <= 0) return app('json')->fail('请输入测试接收用户UID');
            if ((int)$channel['support_user'] !== 1) $uid = 0;
            $type = $uid > 0 ? 1 : 2;
            $templates = app()->make(\app\dao\message\NoticeTemplateDao::class)->getByChannelKey($channelKey);
            $noticeDao = app()->make(\app\dao\message\NoticeConfigDao::class);
            $notice = null;
            foreach ($templates as $template) {
                if ((int)$template['status'] !== 1) continue;
                $candidate = $noticeDao->get((int)$template['notification_id']);
                if ($candidate && (int)$candidate['type'] === $type) {
                    $notice = $candidate;
                    break;
                }
            }
            if (!$notice) return app('json')->fail('请先开启至少一条该渠道的通知模板');
            $data = [];
            preg_match_all('/\{([^}]+)\}/', (string)$notice['variable'], $matches);
            foreach ($matches[1] as $field) $data[$field] = '测试';
            if ($uid > 0 && in_array($channelKey, ['is_sms', 'is_email'], true)) {
                $user = app()->make(\app\services\user\UserServices::class)->getUserInfo($uid);
                if (!$user) return app('json')->fail('测试用户不存在');
                $data['user_phone'] = $user['phone'] ?? '';
                $data['user_email'] = $user['email'] ?? '';
            }
            $results = \app\services\message\Notice::to($uid)->channel($channelKey)
                ->sendMsg((string)$notice['mark'], (string)$notice['name'], $data);
            // sendMsg 返回 [channel_key => ['success' => bool, 'error' => string]]；空数组 = 没有渠道实际执行
            $result = $results[$channelKey] ?? null;
            if ($result && $result['success']) {
                return app('json')->success('测试发送成功');
            }
            return app('json')->fail('测试发送失败：' . ($result['error'] ?? '没有匹配到已启用的通知配置'));
        } catch (\Throwable $e) {
            return app('json')->fail('测试发送失败：' . $e->getMessage());
        }
    }
}
