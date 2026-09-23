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

namespace app\services\message\admin;

use app\dao\message\NoticeConfigDao;
use app\dao\message\NoticeTemplateDao;
use app\services\message\pipeline\SendNoticeService;
use app\services\message\pipeline\NoticeReadiness;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder as Form;
use think\facade\Log;
use think\facade\Route as Url;

/**
 * 通知配置管理类
 * Class NoticeConfigServices
 * @package app\services\message\admin
 * @method value($where, $value) 条件获取某个字段的值
 */
class NoticeConfigServices extends BaseService
{

    /**
     * @var NoticeConfigDao
     */
    protected $dao;

    /**
     * @var NoticeTemplateDao
     */
    protected $templateDao;

    /**
     * 获取全部通知配置
     *
     * @return array
     */
    public function getAllNotce(): array
    {
        $notifications = $this->dao->search([])->select()->toArray();
        if (empty($notifications)) {
            return [];
        }

        $notificationIds = array_column($notifications, 'id');
        $channels = $this->templateDao->getByNotificationIds($notificationIds);

        $channelMap = [];
        foreach ($channels as $channel) {
            $channelMap[$channel['notification_id']][$channel['channel_key']] = $channel;
        }

        // 合并渠道支持范围与适配器类名（供发送侧按通知 type 过滤、按类名实例化渠道）
        $supportMap = $this->getChannelSupportMap();

        foreach ($notifications as &$notification) {
            $notificationChannels = array_filter($channelMap[$notification['id']] ?? [],
                fn($key) => $this->allowsChannel($notification, $key), ARRAY_FILTER_USE_KEY);
            foreach ($notificationChannels as $key => &$channel) {
                if (isset($supportMap[$key])) {
                    $channel['support_user'] = $supportMap[$key]['support_user'];
                    $channel['support_platform'] = $supportMap[$key]['support_platform'];
                    $channel['global_status'] = $supportMap[$key]['global_status'];
                    $channel['adapter_class'] = $supportMap[$key]['adapter_class'];
                    $channel['channel_name'] = $supportMap[$key]['channel_name'];
                    $channel['owner_plugin_id'] = $supportMap[$key]['owner_plugin_id'];
                } else {
                    $channel['global_status'] = 0;
                }
            }
            unset($channel);
            $notification['channels'] = $notificationChannels;
        }

        return $notifications;
    }

    /**
     * 获取渠道定义映射 [channel_key => ['support_user'=>int, 'support_platform'=>int, 'global_status'=>int, 'adapter_class'=>string, 'channel_name'=>string]]
     * @return array
     */
    protected function getChannelSupportMap(): array
    {
        try {
            $channelDao = app()->make(\app\dao\message\NoticeChannelDao::class);
            $channels = $channelDao->getList([], 'channel_key, channel_name, support_user, support_platform, status, adapter_class, owner_plugin_id');
            $map = [];
            foreach ($channels as $channel) {
                $map[$channel['channel_key']] = [
                    'support_user'     => (int)($channel['support_user'] ?? 1),
                    'support_platform' => (int)($channel['support_platform'] ?? 1),
                    'global_status' => (int)($channel['status'] ?? 0),
                    'adapter_class' => (string)($channel['adapter_class'] ?? ''),
                    'channel_name' => (string)($channel['channel_name'] ?? ''),
                    'owner_plugin_id' => (string)($channel['owner_plugin_id'] ?? ''),
                ];
            }
            return $map;
        } catch (\Throwable $e) {
            Log::error('获取渠道支持范围失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 判断渠道适配器是否已登记且可用：adapter_class 非空、类存在、归属模块已启用
     * @param array|null $definition 渠道定义行（含 adapter_class / owner_plugin_id 字段）
     * @return bool
     */
    protected function adapterRegistered(?array $definition): bool
    {
        $adapter = (string)($definition['adapter_class'] ?? '');
        if ($adapter === '' || !class_exists($adapter)) {
            return false;
        }
        return \crmeb\plugin\ModuleStateChecker::isOwnerActive((string)($definition['owner_plugin_id'] ?? ''));
    }

    /**
     * NoticeConfigServices constructor.
     * @param NoticeConfigDao $dao
     * @param NoticeTemplateDao $templateDao
     */
    public function __construct(NoticeConfigDao $dao, NoticeTemplateDao $templateDao)
    {
        $this->dao = $dao;
        $this->templateDao = $templateDao;
    }

    /**
     * 单个配置
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneNotce(array $where)
    {
        return $this->dao->getOne($where);
    }

    /**
     * 后台获取列表
     *
     * 从 eb_notice_config 获取通知列表，并从 eb_notice_template 合并各渠道的开关状态。
     * 解决列表页渠道开关因读写不同表导致状态不生效的问题。
     *
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotList(array $where)
    {
        $notifications = $this->dao->getList($where);
        if (empty($notifications)) {
            return [];
        }

        $notificationIds = array_column($notifications, 'id');
        $channels = $this->templateDao->getByNotificationIds($notificationIds);

        // 按 notification_id + channel_key 组织为嵌套结构，前端通过 row.channels[channelKey].status 读取
        $channelMap = [];
        foreach ($channels as $channel) {
            $channelMap[$channel['notification_id']][$channel['channel_key']] = $channel;
        }

        $configDao = app()->make(\app\dao\message\NoticeChannelDao::class);
        $dbConfigs = $configDao->getList([], 'channel_key, status, support_user, support_platform, adapter_class, owner_plugin_id');
        $dbConfigMap = [];
        foreach ($dbConfigs as $cfg) {
            $dbConfigMap[$cfg['channel_key']] = $cfg;
        }

        foreach ($notifications as &$notification) {
            $rowType = (int)$notification['type'];
            $notification['channels'] = array_filter($channelMap[$notification['id']] ?? [], function ($channel) use ($dbConfigMap, $rowType, $notification) {
                $key = $channel['channel_key'];
                $definition = $dbConfigMap[$key] ?? null;
                return $this->allowsChannel($notification, $key)
                    && NoticeReadiness::channelReasons($key, $rowType, $definition, $this->adapterRegistered($definition)) === [];
            });
        }

        return $notifications;
    }

    /**
     * 获取单条数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotInfo(array $where)
    {
        $type = (string)($where['type'] ?? '');
        $definitions = app()->make(\app\dao\message\NoticeChannelDao::class)->getList([], 'channel_key, status, support_user, support_platform, adapter_class, owner_plugin_id');
        $definitionMap = array_column($definitions, null, 'channel_key');
        unset($where['type']);
        $info = $this->dao->getOne($where);
        // 记录不存在时抛出异常（如前端传入无效 id），避免返回空数组导致前端页面无提示空白
        if (!$info) {
            throw new AdminException('通知配置不存在');
        }
        $info = $info->toArray();
        $info['scene_title'] = $info['title'];

        // 只有核心 SQL / 插件 Installer 写入的模板才能在设置页出现。
        $allChannels = $this->templateDao->getByNotificationIds([$info['id']]);
        $channelMap = [];
        foreach ($allChannels as $ch) {
            if (!$this->allowsChannel($info, $ch['channel_key'])) continue;
            if (NoticeReadiness::channelReasons($ch['channel_key'], (int)$info['type'],
                $definitionMap[$ch['channel_key']] ?? null, $this->adapterRegistered($definitionMap[$ch['channel_key']] ?? null))) continue;
            $channelMap[$ch['channel_key']] = $ch;
            $info[$ch['channel_key']] = $ch['status'];
        }
        $info['channels'] = $channelMap;
        if ($type !== '' && !isset($channelMap[$type])) throw new AdminException('该通知未声明此渠道');
        if ($type === '') $type = (string)(array_key_first($channelMap) ?? '');
        $info['selected_channel'] = $type;
        if ($type === '') {
            $info['variables'] = $this->parseVariableList($info['variable'] ?? '');
            $info['readiness_reasons'] = [];
            return $info;
        }

        // 获取当前选中渠道的详情，填充表单各字段
        $channel = $this->templateDao->getByNotificationAndChannel($info['id'], $type);
        if ($channel) {
            $channel = $channel->toArray();
            // 约定式字段映射：is_xxx 渠道 → xxx_text / xxx_title
            // 新增渠道无需修改此处，自动适用（前提：前端表单字段遵循相同约定）
            $shortKey = substr($type, 3); // 去掉 'is_' 前缀
            $info['status'] = $channel['status'];
            $info['content'] = $channel['template_content'] ?? '';
            $info['title'] = $channel['template_title'] ?? '';
            $info['channel_title'] = $channel['template_title'] ?? '';
            $info[$shortKey . '_text'] = $channel['template_content'] ?? '';
            $info[$shortKey . '_title'] = $channel['template_title'] ?? '';

            // extra JSON 内字段直接合并到 $info，如 webhook / tempkey / tempid / key_list / sms_id / url
            if (!empty($channel['extra'])) {
                $extra = json_decode($channel['extra'], true) ?? [];
                foreach ($extra as $key => $value) {
                    $info[$key] = $value;
                }
            }
        }

        // 根据渠道适配器类 getExtraFields() 声明，为未从 extra JSON 中读到的字段补默认值
        $adapterClass = (string)($definitionMap[$type]['adapter_class'] ?? '');
        $channelClass = $adapterClass !== '' && class_exists($adapterClass) ? $adapterClass : null;
        if ($channelClass && method_exists($channelClass, 'getExtraFields')) {
            foreach ($channelClass::getExtraFields() as $field => $default) {
                $info[$field] = $info[$field] ?? $default;
            }
        }

        // 解析 variable 字段为变量列表，供前端编辑模板时点击插入 {key}
        // 与 key_list（微信/小程序模板字段）区分，独立用 variables 字段返回
        $info['variables'] = $this->parseVariableList($info['variable'] ?? '');
        $template = $channel ? $channel : ($channelMap[$type] ?? []);
        $smsDriver = $this->enabledSmsDriverKey();
        $hasRecipient = (int)$info['type'] !== 2 || $this->hasPlatformRecipient();
        $info['readiness_reasons'] = NoticeReadiness::reasons($type, $template, $info['variable'] ?? '',
            (int)$info['type'], $hasRecipient, $smsDriver, $this->enabledEmailDriverKey());

        return $info;
    }

    /**
     * 解析 variable 字段为变量列表
     *
     * variable 字段格式：{key1}描述,{key2}描述 （兼容旧版数据）
     * 描述由扫描服务从业务监听器 sendMsg 数组的行内注释提取落库。
     * 解析后返回 [{key, label}, ...]，label 为描述，无描述时用 key 本身
     *
     * @param string $variable
     * @return array
     */
    protected function parseVariableList(string $variable): array
    {
        $list = [];
        if (empty($variable)) {
            return $list;
        }
        foreach (explode(',', $variable) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            if (preg_match('/^\{([^}]+)\}(.*)$/', $item, $m)) {
                $list[] = [
                    'key'   => $m[1],
                    'label' => $m[2] !== '' ? $m[2] : $m[1],
                ];
            }
        }
        return $list;
    }

    /**
     * 保存数据
     * @param array $data
     * @return bool|\crmeb\basic\BaseModel|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveData(array $data)
    {
        $type = $data['type'];
        $id = $data['id'];
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('数据不存在');
        }
        $channel = $this->templateDao->getByNotificationAndChannel($id, $type);
        $this->assertDeclaredTemplate($info, $type, $channel);
        $global = app()->make(\app\dao\message\NoticeChannelDao::class)->getByChannelKey($type);
        $scopeReasons = NoticeReadiness::channelReasons($type, (int)$info['type'], $global, $this->adapterRegistered($global));
        if ($scopeReasons) throw new AdminException('通知渠道不可编辑：' . implode('、', $scopeReasons));

        $notificationUpdate = [];
        if (isset($data['name'])) $notificationUpdate['name'] = $data['name'];
        if (isset($data['scene_title'])) $notificationUpdate['title'] = $data['scene_title'];

        // 根据渠道适配器类的 getExtraFields() 声明，从表单数据提取额外配置字段
        // 新增渠道只需在适配器类覆写 getExtraFields()，无需修改此处
        $channelExtra = [];
        $adapterClass = (string)($global['adapter_class'] ?? '');
        $channelClass = $adapterClass !== '' && class_exists($adapterClass) ? $adapterClass : null;
        if ($channelClass && method_exists($channelClass, 'getExtraFields')) {
            foreach ($channelClass::getExtraFields() as $field => $default) {
                if (array_key_exists($field, $data)) $channelExtra[$field] = $data[$field];
            }
        }

        $previousExtra = $channel && !empty($channel['extra']) ? (json_decode((string)$channel['extra'], true) ?: []) : [];
        $channelExtra = array_merge($previousExtra, $channelExtra);
        $shortKey = substr($type, 3);
        if ($type === 'is_sms' && isset($data['sms_id']) && $data['sms_id'] !== ($previousExtra['sms_id'] ?? '')) {
            $channelExtra['sms_driver_key'] = $this->enabledSmsDriverKey();
        }
        $channelData = [
            'notification_id' => $id,
            'channel_key' => $type,
            'status' => $data['status'] ?? $data[$type] ?? 2,
            'template_title' => $data['channel_title'] ?? $data[$shortKey . '_title'] ?? $data['title'] ?? '',
            'template_content' => $data['content'] ?? $data[$shortKey . '_text'] ?? '',
            'extra' => !empty($channelExtra) ? json_encode($channelExtra) : '',
            'updated_at' => time(),
        ];
        if (!in_array((int)$channelData['status'], [1, 2], true)) throw new AdminException('通知状态无效');
        $this->assertVerificationSmsEnabled($info, $type, (int)$channelData['status']);
        if ((int)$channelData['status'] === 1) {
            $reasons = NoticeReadiness::reasons($type, $channelData, $info['variable'] ?? '', (int)$info['type'],
                (int)$info['type'] !== 2 || $this->hasPlatformRecipient(), $this->enabledSmsDriverKey(), $this->enabledEmailDriverKey());
            if ($reasons) throw new AdminException('渠道尚未就绪：' . implode('、', array_unique($reasons)));
        }

        if ($notificationUpdate) $this->dao->update((int)$id, $notificationUpdate);

        $res = $this->templateDao->update($channel->id, $channelData);

        if ($res) {
            app()->make(SendNoticeService::class)->clearCache();
        }
        return $res;
    }

    /**
     * 列表快捷开关与编辑表单共享开启边界。
     * @param int $id 通知配置 ID
     * @param string $key 渠道标识
     * @param int $status 1开启、2关闭
     * @return bool
     */
    public function setChannelStatus(int $id, string $key, int $status): bool
    {
        if (!in_array($status, [1, 2], true)) throw new AdminException('通知状态无效');
        $notice = $this->dao->get($id);
        if (!$notice) throw new AdminException('通知配置不存在');
        $this->assertVerificationSmsEnabled($notice, $key, $status);
        $template = $this->templateDao->getByNotificationAndChannel($id, $key);
        $this->assertDeclaredTemplate($notice, $key, $template);
        $global = app()->make(\app\dao\message\NoticeChannelDao::class)->getByChannelKey($key);
        $scopeReasons = NoticeReadiness::channelReasons($key, (int)$notice['type'], $global, $this->adapterRegistered($global));
        if ($scopeReasons) throw new AdminException('通知渠道不可编辑：' . implode('、', $scopeReasons));
        if ($status === 1) {
            $reasons = NoticeReadiness::reasons($key, $template ? $template->toArray() : [], $notice['variable'] ?? '',
                (int)$notice['type'], (int)$notice['type'] !== 2 || $this->hasPlatformRecipient(),
                $this->enabledSmsDriverKey(), $this->enabledEmailDriverKey());
            if ($reasons) throw new AdminException('渠道尚未就绪：' . implode('、', array_unique($reasons)));
        }
        $result = $this->templateDao->updateStatus($id, $key, $status);
        app()->make(SendNoticeService::class)->clearCache();
        return $result;
    }

    /**
     * 核心验证码只通过短信发送；其他消息以其持久化模板为渠道声明。
     * @param array|\ArrayAccess $notice 通知配置
     * @param string $key 渠道标识
     * @return bool
     */
    protected function allowsChannel($notice, string $key): bool
    {
        return (string)$notice['mark'] !== 'verify_code' || (int)$notice['type'] !== 1 || $key === 'is_sms';
    }

    /**
     * 设置和快捷开关不得创建未由安装 SQL / Installer 登记的渠道模板。
     * @param array|\ArrayAccess $notice 通知配置
     * @param string $key 渠道标识
     * @param mixed $template 模板记录
     * @return void
     */
    protected function assertDeclaredTemplate($notice, string $key, $template): void
    {
        if (!$template || !$this->allowsChannel($notice, $key)) throw new AdminException('该通知未声明此渠道');
    }

    /**
     * 验证码直发依赖短信模板，后台不得关闭该核心通知。
     * @param array|\ArrayAccess $notice 通知配置
     * @param string $key 渠道标识
     * @param int $status 模板状态
     * @return void
     */
    protected function assertVerificationSmsEnabled($notice, string $key, int $status): void
    {
        if ($key === 'is_sms' && (string)$notice['mark'] === 'verify_code'
            && (int)$notice['type'] === 1 && $status !== 1) {
            throw new AdminException('短信验证码必须保持开启');
        }
    }

    /**
     * 获取tempid
     * @param $type
     * @return array
     */
    public function getTempId($type)
    {
        return $this->dao->getTempId($type);
    }

    /**
     * 获取tempkey
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTempKey($type)
    {
        return $this->dao->getTempKey($type);
    }

    /** @return string 当前短信驱动标识 */
    protected function enabledSmsDriverKey(): string
    {
        try {
            return (string)(app()->make(\app\services\driver\sms\SmsDriverServices::class)->getEnabledDriver()['driver_key'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** @return string 当前邮件驱动标识 */
    protected function enabledEmailDriverKey(): string
    {
        try {
            return (string)(app()->make(\app\services\driver\email\EmailDriverServices::class)->getEnabledDriver()['driver_key'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** @return bool 是否存在平台站内信接收人 */
    protected function hasPlatformRecipient(): bool
    {
        foreach ((array)event('AdminNoticeRecipientResolve', []) as $recipients) {
            if (is_array($recipients) && $recipients) return true;
        }
        return false;
    }
}
