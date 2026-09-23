<?php
namespace app\adminapi\controller\system;

use app\services\system\QueueProbeServices;

/** 匿名队列诊断接口，不依赖后台登录上下文。 */
class QueueProbeController
{
    /** @var QueueProbeServices 队列诊断服务 */
    protected $services;

    /**
     * 注入诊断服务，创建频率由专用路由中间件限制。
     * @param QueueProbeServices $services 诊断服务
     */
    public function __construct(QueueProbeServices $services)
    {
        $this->services = $services;
    }

    /** 创建两项探针任务。
     * @return \think\Response
     */
    public function create()
    {
        return app('json')->success($this->services->create())->header(['Cache-Control' => 'no-store']);
    }

    /** 查询测试结果。
     * @param string $id 测试标识
     * @return \think\Response
     */
    public function read(string $id)
    {
        return app('json')->success($this->services->read($id))->header(['Cache-Control' => 'no-store']);
    }
}
