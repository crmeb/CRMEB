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
namespace app\adminapi\controller\setting;

use app\adminapi\controller\AuthController;
use app\services\message\admin\NoticeConfigServices;
use crmeb\services\CacheService;
use think\facade\App;

/**
 * 通知配置控制器
 * 负责通知配置的列表、详情、保存与渠道开关管理
 * @package app\adminapi\controller\setting
 */
class NoticeConfigController extends AuthController
{
    /**
     * @var NoticeConfigServices
     */
    protected $services;


    /**
     * 构造方法
     * @param App $app
     * @param NoticeConfigServices $services
     */
    public function __construct(App $app, NoticeConfigServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['type', ''],
        ]);
        return app('json')->success($this->services->getNotList($where));
    }

    /**
     * 显示编辑
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function info()
    {
        $where = $this->request->getMore([
            ['type', ''],
            ['id', 0]
        ]);
        if (!$where['id']) return app('json')->fail('参数错误');
        return app('json')->success($this->services->getNotInfo($where));
    }

    /**
     * 保存通知配置（渠道级提交）
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['type', ''],           // 渠道标识 is_wechat/is_routine/is_sms/is_system/is_ent_wechat/is_dingtalk/is_email
            ['name', ''],
            ['title', ''],
            ['scene_title', ''],
            ['channel_title', ''],
            ['content', ''],        // 模板内容
            ['status', 2],          // 渠道开关 1=开启 2=关闭
            ['tempkey', ''],        // 微信/小程序模板编号
            ['tempid', ''],         // 微信/小程序模板ID
            ['sms_id', ''],         // 短信模板ID
            ['key_list', []],       // 微信/小程序模板字段列表
            ['wechat_link', ''],    // 公众号跳转链接
            ['wechat_to_routine', ''], // 公众号跳转小程序
            ['routine_link', ''],   // 小程序跳转链接
            ['dingtalk_webhook', ''], // 钉钉Webhook
            ['url', ''],            // 企业微信URL
            // 以下为旧字段，前端改造后可删除
            ['is_system', 0],
            ['is_app', 0],
            ['is_wechat', 0],
            ['is_routine', 0],
            ['is_sms', 0],
            ['is_ent_wechat', 0],
            ['is_dingtalk', 0],
            ['is_email', 0],
            ['system_title', ''],
            ['system_text', ''],
            ['ent_wechat_text', ''],
            ['wechat_id', ''],
            ['routine_id', ''],
            ['mark', ''],
            ['sms_text', ''],
            ['dingtalk_title', ''],
            ['dingtalk_text', ''],
            ['email_title', ''],
            ['email_text', ''],
            ['webhook', ''],
        ]);
        if (!$data['id']) return app('json')->fail('参数错误');
        // 约定式字段映射：is_xxx 渠道 → xxx_text / xxx_title，新增渠道无需修改
        // 前端表单可能传了专属字段（如 system_text）或通用字段（如 content），优先取专属字段
        $shortKey = substr($data['type'], 3); // 去掉 'is_' 前缀
        if (in_array($shortKey, ['system', 'email'], true)) {
            $data['channel_title'] = $data[$shortKey . '_title'];
        }
        if (in_array($shortKey, ['system', 'sms', 'ent_wechat', 'dingtalk', 'email'], true)) {
            $data['content'] = $data[$shortKey . '_text'];
        }
        if ($this->services->saveData($data)) {
            return app('json')->success('修改成功');
        } else {
            return app('json')->fail('修改失败');
        }
    }

    /**
     * 修改消息状态
     * @param $type
     * @param $status
     * @param $id
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setStatus($type, $status, $id)
    {
        if ($type === '' || $status === '' || $status === null || empty($id)) return app('json')->fail('参数错误');

        $this->services->setChannelStatus((int)$id, (string)$type, (int)$status);
        return app('json')->success('设置成功');
    }
}
