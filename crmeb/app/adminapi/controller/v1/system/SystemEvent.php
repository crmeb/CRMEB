<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use app\services\system\SystemEventServices;
use think\facade\App;
use think\facade\Env;

class SystemEvent extends AuthController
{
    public function __construct(App $app, SystemEventServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 自定事件类型
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function getMarkList()
    {
        return app('json')->success($this->services->getMarkList());
    }

    /**
     * 自定事件列表
     * @return \think\Response
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function getEventList()
    {
        return app('json')->success($this->services->getEventList());
    }

    /**
     * 自定事件详情
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function getEventInfo($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        return app('json')->success($this->services->getEventInfo($id));
    }

    /**
     * 自定事件添加编辑
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function saveEvent()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['mark', ''],
            ['content', ''],
            ['is_open', 0],
            ['customCode', ''],
            ['password', ''],
        ]);
        if ($data['name'] == '') return app('json')->fail('请填写事件名称');
        if ($data['mark'] == '') return app('json')->fail('请选择事件类型');
        if (!Env::get('app_debug', false)) return app('json')->fail('生产环境下无法新增和修改自定义内容，如需修改请修改.env文件中app_debug项为true');
        if ($data['password'] === '') return app('json')->fail('密码不能为空');
        if (config('filesystem.password') !== $data['password']) return app('json')->fail('密码错误');
        $adminInfo = $this->request->adminInfo();
        if (!$adminInfo) return app('json')->fail('非法操作');
        if ($adminInfo['level'] != 0) return app('json')->fail('仅超级管理员可以操作定时任务');
        if (!$this->isSafePhpCode($data['customCode'])) return app('json')->fail('自定义内容存在危险代码，请检查代码');
        $this->services->saveEvent($data);
        return app('json')->success(100000);
    }

    /**
     * 检查是否包含删除表，删除表数据，删除文件，修改文件内容以及后缀，执行命令等操作的关键词
     * @param $code
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    function isSafePhpCode($code)
    {
        // 检查是否包含删除表，删除表数据，删除文件，修改文件内容以及后缀，执行命令等操作的关键词
        $dangerous_keywords = [
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
        ];
        foreach ($dangerous_keywords as $keyword) {
            if (strpos($code, $keyword) !== false) {
                return false;
            }
        }
        return true; // 如果通过所有安全检查，返回 true
    }

    /**
     * 自定事件是否开启开关
     * @param $id
     * @param $is_open
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function setEventStatus($id, $is_open)
    {
        $this->services->setEventStatus($id, $is_open);
        return app('json')->success(100014);
    }

    /**
     * 删除自定事件
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function delEvent($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $this->services->eventDel($id);
        return app('json')->success(100002);
    }
}