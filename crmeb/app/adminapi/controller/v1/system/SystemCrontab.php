<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use app\services\system\crontab\SystemCrontabServices;
use think\facade\App;
use think\facade\Env;

class SystemCrontab extends AuthController
{
    public function __construct(App $app, SystemCrontabServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取定时任务列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTimerList()
    {
        $where = $this->request->getMore([
            ['custom', 0],
        ]);
        $where['is_del'] = 0;
        return app('json')->success($this->services->getTimerList($where));
    }

    /**
     * 获取定时任务详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTimerInfo($id)
    {
        return app('json')->success($this->services->getTimerInfo($id));
    }

    /**
     * 获取定时任务类型
     * @return mixed
     */
    public function getMarkList()
    {
        return app('json')->success($this->services->getMarkList());
    }

    /**
     * 保存定时任务
     * @return mixed
     */
    public function saveTimer()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['mark', ''],
            ['content', ''],
            ['type', 0],
            ['is_open', 0],
            ['month', 0],
            ['week', 0],
            ['day', 0],
            ['hour', 0],
            ['minute', 0],
            ['second', 0],
            ['customCode', ''],
            ['password', ''],
        ]);
        if ($data['mark'] == 'customTimer') {
            if (!Env::get('app_debug', false)) return app('json')->fail('生产环境下无法新增和修改自定义内容，如需修改请修改.env文件中app_debug项为true');
            if ($data['password'] === '') return app('json')->fail('密码不能为空');
            if (config('filesystem.password') !== $data['password']) return app('json')->fail('密码错误');
            $adminInfo = $this->request->adminInfo();
            if (!$adminInfo) return app('json')->fail('非法操作');
            if ($adminInfo['level'] != 0) return app('json')->fail('仅超级管理员可以操作定时任务');
            if (!$this->isSafePhpCode($data['customCode'])) return app('json')->fail('自定义内容存在危险代码，请检查代码');
        }
        $this->services->saveTimer($data);
        return app('json')->success(100000);
    }

    /**
     * 删除定时任务
     * @param $id
     * @return mixed
     */
    public function delTimer($id)
    {
        $this->services->delTimer($id);
        return app('json')->success(100002);
    }

    /**
     * 设置定时任务状态
     * @param $id
     * @param $is_open
     * @return mixed
     */
    public function setTimerStatus($id, $is_open)
    {
        $this->services->setTimerStatus($id, $is_open);
        return app('json')->success(100014);
    }

    /**
     * 检查是否包含删除表，删除表数据，删除文件，修改文件内容以及后缀，执行命令等操作的关键词
     * @param $code
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/6
     */
    function isSafePhpCode($code)
    {
        // 检查是否包含删除表，删除表数据，删除文件，修改文件内容以及后缀，执行命令等操作的关键词
        $dangerous_keywords = array(
            'delete',
            'destroy',
            'DROP TABLE',
            'DELETE FROM',
            'unlink(',
            'fwrite(',
            'shell_exec(',
            'exec(',
            'system(',
            'passthru('
        );
        foreach ($dangerous_keywords as $keyword) {
            if (strpos($code, $keyword) !== false) {
                return false;
            }
        }
        return true; // 如果通过所有安全检查，返回 true
    }

}