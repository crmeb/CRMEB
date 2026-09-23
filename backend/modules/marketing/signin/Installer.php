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
declare(strict_types=1);

namespace modules\marketing\signin;

use think\facade\Db;
use crmeb\module\BaseModuleInstaller;
use app\services\system\admin\SystemRoleServices;

/**
 * 签到活动安装器
 *
 * 菜单结构：
 *   营销 > 每日签到
 *          > 签到配置
 *          > 签到奖励
 */
class Installer extends BaseModuleInstaller
{
    public function getKey(): string  { return 'user_signin'; }
    public function getName(): string { return '签到活动'; }
    public function getType(): string { return 'marketing'; }

    /**
     * 签到相关 system_config 配置键列表
     * 安装时写入"用户签到配置"分组（eng_title = user_sign_config），卸载时一并清理
     */
    private const CONFIG_KEYS = [
        'sign_give_exp', 'sign_status', 'sign_mode', 'sign_remind',
        'sign_remind_time', 'sign_remind_type', 'sign_give_point',
    ];

    /** 签到配置分类标识 */
    private const CONFIG_TAB = 'user_sign_config';

    /**
     * 安装提交后失效菜单/API 权限缓存。
     *
     * @return array 安装结果
     */
    public function install(): array
    {
        $result = parent::install();
        if ($result['status'] === 'success') {
            app()->make(SystemRoleServices::class)->invalidateAuthCache();
        }
        return $result;
    }

    /**
     * 建表
     * 已存在的表幂等跳过
     */
    protected function runMigrate(): void
    {
        // 系统签到奖励表
        if (!$this->hasTable('system_sign_reward')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('system_sign_reward')}` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，0连续签到奖励，1累计签到奖励',
                `days` int(11) NOT NULL DEFAULT '0' COMMENT '天数',
                `point` int(11) NOT NULL DEFAULT '0' COMMENT '赠送积分',
                `exp` int(11) NOT NULL DEFAULT '0' COMMENT '赠送经验',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='系统签到奖励表';");
        }

        // 用户签到记录表
        if (!$this->hasTable('user_sign')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('user_sign')}` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户uid',
                `title` varchar(255) NOT NULL DEFAULT '' COMMENT '签到说明',
                `number` int(11) NOT NULL DEFAULT '0' COMMENT '获得积分',
                `balance` int(11) NOT NULL DEFAULT '0' COMMENT '剩余积分',
                `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
                PRIMARY KEY (`id`),
                KEY `uid` (`uid`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='签到记录表';");
        }
    }

    /**
     * 回滚建表
     */
    protected function rollbackMigrate(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('user_sign')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('system_sign_reward')}`;");
    }

    /**
     * 获取签到插件的通知初值。
     * @return array
     */
    protected function getNoticeDefinitions(): array
    {
        return [
            [
                'mark' => 'sign_remind',
                'type' => 1,
                'name' => '签到提醒',
                'scene' => '向当天未签到的订阅用户发提醒',
                'variables' => [
                    'uid' => '用户ID',
                    'phone' => '手机号',
                    'sign_days' => '天数',
                    'integral' => '积分',
                    'site_name' => '商城名称'
                ],
                'channels' => [
                    'is_system' => [
                        'title' => '签到提醒',
                        'content' => '{site_name}提醒您今天签到领取奖励。',
                        'status' => 1
                    ],
                    'is_sms' => [
                        'title' => '签到提醒',
                        'content' => '{site_name}提醒您今天签到领取奖励。',
                        'extra' => [
                            'sms_id' => '980181'
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * 种子数据：写入签到相关 system_config 配置项
     *
     * 安装时确保"用户签到配置"分组（eng_title = user_sign_config）存在，
     * 并写入 7 条签到配置项（幂等，已存在则跳过）：
     * sign_give_exp / sign_status / sign_mode / sign_remind / sign_remind_time / sign_remind_type / sign_give_point
     *
     * @return int 实际写入的条目数
     */
    protected function seed(): int
    {
        $count = 0;
        $count += app()->make(\app\services\message\pipeline\NoticeSeedServices::class)->seed($this->getOwnerPluginId(), $this->getNoticeDefinitions());

        // 1. 确保配置分类 tab 存在（幂等）
        $tab = $this->ensureConfigTab(self::CONFIG_TAB, [
            'pid'       => (int) Db::table($this->table('system_config_tab'))
                ->where('eng_title', 'system_user_config')->value('id'),
            'title'     => '用户签到配置',
            'eng_title' => self::CONFIG_TAB,
            'status'    => 0,
            'info'      => 0,
            'icon'      => '',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => 0,
            'owner_plugin_id' => $this->getOwnerPluginId(),
        ]);
        if ($tab['created']) {
            $count++;
        }

        // 配置分组只能关联本插件的配置菜单，不能引用核心用户配置的数字 ID。
        $count += $this->linkSignConfigMenu($tab['id']);

        // 2. 写入配置项（幂等）
        $configs = [
            [
                'menu_name'  => 'sign_give_exp',
                'type'       => 'text',
                'input_type' => 'input',
                'parameter'  => '',
                'value'      => '0',
                'info'       => '签到赠送经验',
                'desc'       => '签到赠送用户经验值',
                'sort'       => 0,
            ],
            [
                'menu_name'  => 'sign_status',
                'type'       => 'radio',
                'input_type' => 'input',
                'parameter'  => "1=>开启\n0=>关闭",
                'value'      => '0',
                'info'       => '签到开关',
                'desc'       => '签到开关，商城是否开启签到功能，关闭后隐藏签到入口',
                'sort'       => 100,
            ],
            [
                'menu_name'  => 'sign_mode',
                'type'       => 'radio',
                'input_type' => 'input',
                'parameter'  => "-1=>无限制\n1=>周循环\n0=>月循环",
                'value'      => '"-1"',
                'info'       => '签到模式',
                'desc'       => '无限制，累积和连续签到不会清零，周循环，每周一会清理累积和连续的记录为0，重新开启计算，月循环，每月一号会清理累积和连续的记录为0，重新开启计算',
                'sort'       => 95,
            ],
            [
                'menu_name'  => 'sign_remind',
                'type'       => 'radio',
                'input_type' => 'input',
                'parameter'  => "1=>开启\n0=>关闭",
                'value'      => '0',
                'info'       => '签到提醒',
                'desc'       => '是否开启签到提醒，提醒方式为短信以及站内信',
                'sort'       => 90,
            ],
            [
                'menu_name'  => 'sign_remind_time',
                'type'       => 'text',
                'input_type' => 'time',
                'parameter'  => '',
                'width'      => 100,
                'value'      => '"09:00:00"',
                'info'       => '提醒时间',
                'desc'       => '选择每日未签到提醒的通知时间',
                'sort'       => 0,
            ],
            [
                'menu_name'  => 'sign_remind_type',
                'type'       => 'checkbox',
                'input_type' => 'input',
                'parameter'  => "1=>短信\n2=>站内信",
                'width'      => 100,
                'value'      => '["1","2"]',
                'info'       => '提醒方式',
                'desc'       => '签到每日提醒的提醒方式，支持短信和站内信',
                'sort'       => 80,
            ],
            [
                'menu_name'  => 'sign_give_point',
                'type'       => 'text',
                'input_type' => 'number',
                'parameter'  => '',
                'value'      => '0',
                'info'       => '签到赠送积分',
                'desc'       => '签到赠送积分，每日签到赠送的积分值',
                'sort'       => 75,
            ],
        ];

        $defaults = [
            'config_tab_id' => $tab['id'],
            'upload_type'   => 1,
            'required'      => '',
            'width'         => 0,
            'high'          => 0,
            'status'        => 1,
            'level'         => 0,
            'link_id'       => 0,
            'link_value'    => 0,
        ];

        foreach ($configs as $config) {
            $exists = Db::table($this->table('system_config'))
                ->where('menu_name', $config['menu_name'])
                ->find();
            if ($exists) {
                continue;
            }
            Db::table($this->table('system_config'))->insert(array_merge($defaults, $config));
            $count++;
        }

        // 提醒时间、提醒方式仅在签到提醒开启时展示。
        $count += $this->ensureSignRemindLink();
        $count += $this->seedSignRemindTask();

        return $count;
    }

    /**
     * 回填签到菜单路径及配置分组的真实菜单编号。
     *
     * @param int $tabId 签到配置分组编号
     * @return int 更新记录数
     */
    private function linkSignConfigMenu(int $tabId): int
    {
        if ($tabId <= 0) {
            return 0;
        }
        $owner = $this->getOwnerPluginId();
        $rootId = (int) Db::table($this->table('system_menus'))
            ->where(['unique_auth' => 'admin-marketing-sign', 'auth_type' => 1, 'owner_plugin_id' => $owner])
            ->value('id');
        if (!$rootId) {
            return 0;
        }
        $configId = (int) Db::table($this->table('system_menus'))
            ->where(['pid' => $rootId, 'menu_name' => '签到配置', 'auth_type' => 1, 'owner_plugin_id' => $owner])
            ->value('id');
        if (!$configId) {
            return 0;
        }
        $path = '/setting/system_config/' . $tabId;
        $count = Db::table($this->table('system_menus'))
            ->whereIn('id', [$rootId, $configId])->where('menu_path', '<>', $path)
            ->update(['menu_path' => $path]);
        $tabQuery = Db::table($this->table('system_config_tab'))
            ->where(['id' => $tabId, 'eng_title' => self::CONFIG_TAB, 'owner_plugin_id' => $owner]);
        return $count + $tabQuery->where('menus_id', '<>', $configId)->update(['menus_id' => $configId]);
    }

    /**
     * 补齐签到提醒任务；商家已有同标识任务的时间和开关保持不变。
     *
     * @return int 新增记录数
     */
    private function seedSignRemindTask(): int
    {
        if (Db::table($this->table('system_crontab'))->where('mark', 'signRemind')->find()) {
            return 0;
        }
        Db::table($this->table('system_crontab'))->insert([
            'name' => '签到提醒', 'mark' => 'signRemind',
            'content' => '每隔10分钟执行未签到提醒',
            'type' => 2, 'week' => 1, 'day' => 1, 'hour' => 1, 'minute' => 10, 'second' => 0,
            'last_execution_time' => 0, 'next_execution_time' => 0,
            'add_time' => time(), 'update_time' => time(), 'is_del' => 0, 'is_open' => 1,
            'customCode' => '', 'timeStr' => '',
        ]);
        return 1;
    }

    /**
     * 确保签到提醒配置联动关系。
     *
     * sign_remind=1（开启）时显示 sign_remind_time 和 sign_remind_type。
     * 使用配置键动态查询父项 ID，避免依赖安装顺序或自增 ID。
     */
    private function ensureSignRemindLink(): int
    {
        $linkId = (int)(Db::table($this->table('system_config'))
            ->where('menu_name', 'sign_remind')
            ->value('id') ?: 0);
        if ($linkId <= 0) {
            return 0;
        }

        $count = 0;
        $configs = Db::table($this->table('system_config'))
            ->whereIn('menu_name', ['sign_remind_time', 'sign_remind_type'])
            ->field(['id', 'level', 'link_id', 'link_value'])
            ->select()
            ->toArray();

        foreach ($configs as $config) {
            if ((int)$config['level'] === 1
                && (int)$config['link_id'] === $linkId
                && (int)$config['link_value'] === 1) {
                continue;
            }

            Db::table($this->table('system_config'))
                ->where('id', $config['id'])
                ->update(['level' => 1, 'link_id' => $linkId, 'link_value' => 1]);
            $count++;
        }

        return $count;
    }

    /**
     * 确保配置分类 tab 存在
     *
     * @param string $engTitle tab 的 eng_title
     * @param array  $data     插入数据（仅在 tab 不存在时使用）
     * @return array ['id' => int, 'created' => bool]
     */
    private function ensureConfigTab(string $engTitle, array $data): array
    {
        $existing = Db::table($this->table('system_config_tab'))
            ->where('eng_title', $engTitle)
            ->where('owner_plugin_id', $this->getOwnerPluginId())
            ->find();
        if ($existing) {
            return ['id' => (int)$existing['id'], 'created' => false];
        }
        $id = Db::table($this->table('system_config_tab'))->insertGetId($data);
        return ['id' => (int)$id, 'created' => true];
    }

    /**
     * 获取签到配置分类 ID，用于拼接通用系统配置页路由。
     * 首次安装时分类尚未创建，返回 0 占位，随后由 seed() 回填真实 ID。
     */
    private function getSignConfigTabId(): int
    {
        $tab = Db::table($this->table('system_config_tab'))
            ->where('eng_title', self::CONFIG_TAB)
            ->where('owner_plugin_id', $this->getOwnerPluginId())
            ->find();

        return $tab ? (int)$tab['id'] : 0;
    }

    /**
     * 后台菜单配置
     *
     * 参考其他插件的配置菜单结构：创建签到配置分类后，
     * 菜单统一跳转至 /setting/system_config/{分类ID}。
     */
    protected function getMenuConfig(): array
    {
        return [
            // 营销 > 每日签到
            [
                'parent_key'  => 'core:admin-marketing',
                'menu_name'   => '每日签到',
                'icon'        => 'CRMEB-162-rili-xianxing',
                'controller'  => '',
                'sort'        => 57,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => 'admin-marketing-sign',
                'menu_path'   => '/setting/system_config/' . $this->getSignConfigTabId(),
            ],
            // 营销 > 每日签到 > 签到配置
            [
                'pid'         => '@每日签到',
                'menu_name'   => '签到配置',
                'icon'        => '',
                'controller'  => '',
                'sort'        => 0,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => 'setting-system-config',
                'menu_path'   => '/setting/system_config/' . $this->getSignConfigTabId(),
            ],
            // 营销 > 每日签到 > 签到奖励（通用页面系统，纯 PHP 渲染）
            [
                'pid'        => '@每日签到',
                'menu_name'  => '签到奖励',
                'icon'       => '',
                'controller' => '',
                'sort'       => 0,
                'is_show'    => 1,
                'access'     => 1,
                'auth_type'  => 1,
                'menu_path'  => '/plugin/signin/rewards',
            ],
            // ============== 隐藏权限菜单 ==============
            // 营销 > 每日签到 > 签到奖励列表（隐藏，仅权限认证用）
            [
                'pid'        => '@每日签到',
                'menu_name'  => '签到奖励列表',
                'controller' => 'signin',
                'action'     => 'index',
                'api_url'    => 'marketing/sign/rewards',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'unique_auth' => 'marketing-sign-rewards',
                'is_show'    => 1,
                'access'     => 1,
            ],
            // 营销 > 每日签到 > 添加签到奖励（隐藏，仅权限认证用）
            [
                'pid'        => '@每日签到',
                'menu_name'  => '添加签到奖励',
                'controller' => 'signin',
                'action'     => 'addRewards',
                'api_url'    => 'marketing/sign/add_rewards',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'unique_auth' => 'marketing-sign-rewards-create',
                'is_show'    => 1,
                'access'     => 1,
            ],
            // 营销 > 每日签到 > 编辑签到奖励（隐藏，仅权限认证用）
            [
                'pid'        => '@每日签到',
                'menu_name'  => '编辑签到奖励',
                'controller' => 'signin',
                'action'     => 'editRewards',
                'api_url'    => 'marketing/sign/edit_rewards/<id>',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'unique_auth' => 'marketing-sign-rewards-edit',
                'is_show'    => 1,
                'access'     => 1,
            ],
            // 营销 > 每日签到 > 保存签到奖励（隐藏，仅权限认证用）
            [
                'pid'        => '@每日签到',
                'menu_name'  => '保存签到奖励',
                'controller' => 'signin',
                'action'     => 'saveRewards',
                'api_url'    => 'marketing/sign/save_rewards/<id>',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'unique_auth' => 'marketing-sign-rewards-save',
                'is_show'    => 1,
                'access'     => 1,
            ],
            // 营销 > 每日签到 > 删除签到奖励（隐藏，仅权限认证用）
            [
                'pid'        => '@每日签到',
                'menu_name'  => '删除签到奖励',
                'controller' => 'signin',
                'action'     => 'delRewards',
                'api_url'    => 'marketing/sign/del_rewards/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 2,
                'unique_auth' => 'marketing-sign-rewards-delete',
                'is_show'    => 1,
                'access'     => 1,
            ],
        ];
    }

    /**
     * 启用时回调：重新插入菜单
     */
    public function onEnable(): void
    {
        parent::onEnable();
        app()->make(SystemRoleServices::class)->invalidateAuthCache();
    }

    /**
     * 禁用时回调：隐藏菜单
     */
    public function onDisable(): void
    {
        parent::onDisable();
    }

    /**
     * 模块自有表：签到记录/签到奖励配置表
     * @return array 表名列表（不含前缀）
     */
    protected function getOwnedTables(): array
    {
        return ['user_sign', 'system_sign_reward'];
    }

    /**
     * 清理业务数据
     *
     * 清理：
     * - 签到业务表（user_sign / system_sign_reward）
     * - 签到相关 system_config 配置项（7 条）
     * - 本安装器创建的"用户签到配置"分组（eng_title = user_sign_config）
     *
     * @return int
     */
    protected function cleanData(): int
    {
        $count = app()->make(\app\services\message\pipeline\NoticeSeedServices::class)->clean($this->getOwnerPluginId());
        $count += $this->cleanTable('user_sign');
        $count += $this->cleanTable('system_sign_reward');

        // 清理签到 system_config 配置项
        $count += Db::table($this->table('system_config'))
            ->whereIn('menu_name', self::CONFIG_KEYS)
            ->delete();

        // 删除本安装器创建的配置分类
        $count += Db::table($this->table('system_config_tab'))
            ->where('eng_title', self::CONFIG_TAB)
            ->delete();

        $count += Db::table($this->table('system_crontab'))->where('mark', 'signRemind')->delete();

        return $count;
    }
}
