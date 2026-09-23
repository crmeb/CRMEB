<?php
declare(strict_types=1);

namespace app\services\message\pipeline;

use app\dao\message\NoticeChannelDao;
use app\dao\message\NoticeConfigDao;
use app\dao\message\NoticeTemplateDao;

/** 插件通知初值与归属管理。 */
class NoticeSeedServices
{
    /** @var NoticeConfigDao */
    protected NoticeConfigDao $configDao;
    /** @var NoticeTemplateDao */
    protected NoticeTemplateDao $templateDao;
    /** @var NoticeChannelDao */
    protected NoticeChannelDao $channelDao;

    /**
     * @param NoticeConfigDao $configDao
     * @param NoticeTemplateDao $templateDao
     * @param NoticeChannelDao $channelDao
     */
    public function __construct(NoticeConfigDao $configDao, NoticeTemplateDao $templateDao, NoticeChannelDao $channelDao)
    {
        $this->configDao = $configDao;
        $this->templateDao = $templateDao;
        $this->channelDao = $channelDao;
    }

    /**
     * 检查种子中同一通知归属和实际可用变量。
     * @param array $definitions
     * @return int
     */
    public static function validateDefinitions(array $definitions): int
    {
        $seen = [];
        foreach ($definitions as $definition) {
            $mark = $definition['mark'] ?? '';
            $type = (int)($definition['type'] ?? 0);
            if (!is_string($mark) || !preg_match('/^[a-z][a-z0-9_]{0,49}$/', $mark) || !in_array($type, [1, 2], true)) {
                throw new \InvalidArgumentException('通知 mark/type 无效');
            }
            $identity = $mark . ':' . $type;
            if (isset($seen[$identity])) {
                throw new \InvalidArgumentException("通知 {$identity} 重复声明");
            }
            $seen[$identity] = true;
            $variables = $definition['variables'] ?? [];
            foreach ($definition['channels'] ?? [] as $key => $channel) {
                if (!preg_match('/^is_[a-z_]+$/', (string)$key) || !in_array((int)($channel['status'] ?? 2), [1, 2], true)) {
                    throw new \InvalidArgumentException("通知 {$identity} 渠道配置无效");
                }
                if ($key !== 'is_system' && (int)($channel['status'] ?? 2) === 1) {
                    throw new \InvalidArgumentException("通知 {$identity} 外部渠道不得默认开启");
                }
                preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', (string)($channel['content'] ?? '') . (string)($channel['title'] ?? ''), $matches);
                foreach ($matches[1] as $variable) {
                    if (!array_key_exists($variable, $variables)) {
                        throw new \InvalidArgumentException("通知 {$identity} 使用了未声明变量 {$variable}");
                    }
                }
                foreach (($channel['extra']['key_list'] ?? []) as $variable) {
                    if (!array_key_exists($variable, $variables)) {
                        throw new \InvalidArgumentException("通知 {$identity} 映射了未声明变量 {$variable}");
                    }
                }
            }
        }
        return count($definitions);
    }

    /**
     * 为部分 JSON 配置补缺；保留商户已经写入的值及映射。
     * @param array $existing
     * @param array $defaults
     * @return array
     */
    public static function mergeEmptyExtra(array $existing, array $defaults): array
    {
        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $existing) || $existing[$key] === '' || $existing[$key] === null) {
                $existing[$key] = $default;
            } elseif (is_array($existing[$key]) && is_array($default)) {
                $existing[$key] = self::mergeEmptyExtra($existing[$key], $default);
            }
        }
        return $existing;
    }

    /**
     * 幂等登记插件通知；只补空值，不覆盖已编辑配置。
     * @param string $owner canonical 插件 ID
     * @param array $definitions
     * @return int
     */
    public function seed(string $owner, array $definitions): int
    {
        self::validateDefinitions($definitions);
        $count = 0;
        foreach ($definitions as $definition) {
            $record = $this->configDao->getOne(['mark' => $definition['mark'], 'type' => $definition['type']]);
            $variable = implode(',', array_map(
                static fn($key, $label) => '{' . $key . '}' . $label,
                array_keys($definition['variables'] ?? []),
                array_values($definition['variables'] ?? [])
            ));
            if ($record) {
                $existingOwner = (string)($record['owner_plugin_id'] ?? '');
                if ($existingOwner === '') {
                    // 只认领同一 mark/type 的无归属行；已归属其他插件的行仍受保护。
                    if ($this->configDao->claimUnowned((int)$record['id'], $owner)) {
                        $count++;
                    } else {
                        $latest = $this->configDao->getOne(['mark' => $definition['mark'], 'type' => $definition['type']]);
                        if ((string)($latest['owner_plugin_id'] ?? '') !== $owner) {
                            throw new \RuntimeException("通知 {$definition['mark']}/{$definition['type']} 归属发生变更");
                        }
                    }
                } elseif ($existingOwner !== $owner) {
                    throw new \RuntimeException("通知 {$definition['mark']}/{$definition['type']} 已归属 {$existingOwner}");
                }
                $update = [];
                foreach (['name' => $definition['name'], 'title' => $definition['scene'] ?? '', 'variable' => $variable] as $field => $value) {
                    if (empty($record[$field]) && $value !== '') {
                        $update[$field] = $value;
                    }
                }
                if ($update) {
                    $this->configDao->update((int)$record['id'], $update);
                    $count++;
                }
                $id = (int)$record['id'];
            } else {
                $created = $this->configDao->save([
                    'mark' => $definition['mark'], 'type' => $definition['type'],
                    'name' => $definition['name'], 'title' => $definition['scene'] ?? '',
                    'variable' => $variable, 'owner_plugin_id' => $owner, 'add_time' => time(),
                ]);
                $id = (int)$created->id;
                $count++;
            }

            foreach ($definition['channels'] ?? [] as $key => $channel) {
                $existing = $this->templateDao->getByNotificationAndChannel($id, $key);
                $extra = $channel['extra'] ?? [];
                if ($key === 'is_sms' && !empty($extra['sms_id']) && empty($extra['sms_driver_key'])) {
                    $extra['sms_driver_key'] = 'yihaotong';
                }
                $defaults = [
                    'template_title' => $channel['title'] ?? '',
                    'template_content' => $channel['content'] ?? '',
                    'extra' => $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : '',
                ];
                if ($existing) {
                    $update = [];
                    foreach ($defaults as $field => $value) {
                        if ($field === 'extra' && $extra) {
                            $current = json_decode((string)($existing['extra'] ?? ''), true) ?: [];
                            $merged = self::mergeEmptyExtra($current, $extra);
                            if ($merged !== $current) $update['extra'] = json_encode($merged, JSON_UNESCAPED_UNICODE);
                            continue;
                        }
                        if (empty($existing[$field]) && $value !== '') {
                            $update[$field] = $value;
                        }
                    }
                    if ($update) {
                        $update['updated_at'] = time();
                        $this->templateDao->update((int)$existing['id'], $update);
                        $count++;
                    }
                } else {
                    $this->templateDao->save(array_merge($defaults, [
                        'notification_id' => $id, 'channel_key' => $key,
                        'status' => $channel['status'] ?? 2, 'created_at' => time(), 'updated_at' => time(),
                    ]));
                    $count++;
                }
            }
        }
        if ($count) {
            app()->make(SendNoticeService::class)->clearCache();
        }
        return $count;
    }

    /**
     * [TEMPORARY] 以插件定义为准，覆盖式刷新插件通知与渠道模板。
     *
     * TEMPORARY-PLUGIN-NOTICE-REFRESH：临时功能——供插件列表"刷新消息模板"按钮调用，
     * 用于插件代码中 getNoticeDefinitions 调整后将定义同步落库，后续随该按钮一并移除。
     *
     * 与 seed() 的"只补空、不覆盖"语义不同，本方法以代码定义为真源：
     * - 通知行：覆盖 name / title(场景) / variable（按 definitions.variables 重建）；
     * - 渠道模板行：覆盖 template_title / template_content；extra 按键合并——
     *   定义中非空的键以定义为准覆盖，定义未声明或为空的键保留现值
     *   （保护后台已填的 tempid / 短信模板ID 等运维配置）；
     * - 渠道开关 status 不动（由后台管理）；
     * - 定义中已移除的渠道，其模板行同步删除；
     * - 定义中已移除的通知（仅限本插件拥有的配置行），连同其全部渠道模板行一并删除。
     *
     * @param string $owner canonical 插件 ID
     * @param array $definitions getNoticeDefinitions 返回结构
     * @return int 变更条数
     */
    public function refreshDefinitions(string $owner, array $definitions): int
    {
        self::validateDefinitions($definitions);
        $count = 0;
        // 代码定义当前声明的通知集合（mark:type），用于清理已从定义中移除的通知
        $declaredNotifications = [];
        foreach ($definitions as $definition) {
            $declaredNotifications[$definition['mark'] . ':' . $definition['type']] = true;
            $record = $this->configDao->getOne(['mark' => $definition['mark'], 'type' => $definition['type']]);
            $variable = implode(',', array_map(
                static fn($key, $label) => '{' . $key . '}' . $label,
                array_keys($definition['variables'] ?? []),
                array_values($definition['variables'] ?? [])
            ));
            if ($record) {
                $existingOwner = (string)($record['owner_plugin_id'] ?? '');
                if ($existingOwner !== '' && $existingOwner !== $owner) {
                    throw new \RuntimeException("通知 {$definition['mark']}/{$definition['type']} 已归属 {$existingOwner}，不允许刷新");
                }
                // 内容一致时不写库，保证重复刷新幂等（不重复计数）
                $configUpdate = [];
                foreach ([
                    'name' => $definition['name'],
                    'title' => $definition['scene'] ?? '',
                    'variable' => $variable,
                ] as $field => $value) {
                    if ((string)($record[$field] ?? '') !== (string)$value) {
                        $configUpdate[$field] = $value;
                    }
                }
                if ($configUpdate) {
                    $this->configDao->update((int)$record['id'], $configUpdate);
                    $count++;
                }
                $id = (int)$record['id'];
            } else {
                $created = $this->configDao->save([
                    'mark' => $definition['mark'], 'type' => $definition['type'],
                    'name' => $definition['name'], 'title' => $definition['scene'] ?? '',
                    'variable' => $variable, 'owner_plugin_id' => $owner, 'add_time' => time(),
                ]);
                $id = (int)$created->id;
                $count++;
            }

            // 登记定义中声明的渠道，并同步删除该通知下已从定义中移除的渠道模板行
            $declaredChannels = [];
            foreach ($definition['channels'] ?? [] as $key => $channel) {
                $declaredChannels[$key] = true;
                $existing = $this->templateDao->getByNotificationAndChannel($id, $key);
                $extra = $channel['extra'] ?? [];
                if ($key === 'is_sms' && !empty($extra['sms_id']) && empty($extra['sms_driver_key'])) {
                    $extra['sms_driver_key'] = 'yihaotong';
                }
                $defaults = [
                    'template_title' => $channel['title'] ?? '',
                    'template_content' => $channel['content'] ?? '',
                    'extra' => $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : '',
                ];
                if ($existing) {
                    $update = [];
                    // 标题/内容以代码定义为准，无条件覆盖
                    foreach (['template_title', 'template_content'] as $field) {
                        if ((string)$defaults[$field] !== (string)($existing[$field] ?? '')) {
                            $update[$field] = $defaults[$field];
                        }
                    }
                    // extra：定义中非空的键覆盖，其余保留现值；比较前递归按键排序，保证幂等（键序不同不算变更）
                    $normalize = static function (array $map) use (&$normalize): array {
                        ksort($map);
                        foreach ($map as $sortKey => $sortValue) {
                            if (is_array($sortValue)) {
                                $map[$sortKey] = $normalize($sortValue);
                            }
                        }
                        return $map;
                    };
                    $current = json_decode((string)($existing['extra'] ?? ''), true) ?: [];
                    $merged = $current;
                    foreach ($extra as $extraKey => $extraValue) {
                        if ($extraValue !== '' && $extraValue !== null && $extraValue !== []) {
                            $merged[$extraKey] = $extraValue;
                        } elseif (!array_key_exists($extraKey, $merged)) {
                            $merged[$extraKey] = $extraValue;
                        }
                    }

                    $update['extra'] = json_encode($normalize($merged), JSON_UNESCAPED_UNICODE);

                    if ($update) {
                        $update['updated_at'] = time();
                        $this->templateDao->update((int)$existing['id'], $update);
                        $count++;
                    }
                } else {
                    $this->templateDao->save(array_merge($defaults, [
                        'notification_id' => $id, 'channel_key' => $key,
                        'status' => $channel['status'] ?? 2, 'created_at' => time(), 'updated_at' => time(),
                    ]));
                    $count++;
                }
            }

            // 删除定义中已移除的渠道模板行（仅删模板行，不触碰通知配置与发送/已读历史）
            $existingChannels = $this->templateDao->getByNotificationId($id);
            foreach ($existingChannels as $existingChannel) {
                if (isset($declaredChannels[$existingChannel['channel_key']])) {
                    continue;
                }
                $this->templateDao->deleteByNotificationAndChannel($id, $existingChannel['channel_key']);
                $count++;
            }
        }

        // 删除定义中已移除的通知（仅限本插件拥有的配置行；连同其全部渠道模板行一并删除）
        $ownedNotifications = $this->configDao->getList(['owner_plugin_id' => $owner]);
        foreach ($ownedNotifications as $owned) {
            $identity = $owned['mark'] . ':' . $owned['type'];
            if (isset($declaredNotifications[$identity])) {
                continue;
            }
            $count += $this->templateDao->deleteByNotificationId((int)$owned['id']);
            $this->configDao->deleteOwned((int)$owned['id'], $owner);
            $count++;
        }

        if ($count) {
            app()->make(SendNoticeService::class)->clearCache();
        }
        return $count;
    }

    /**
     * 幂等登记插件渠道定义。
     * @param string $owner canonical 插件 ID
     * @param array $channels
     * @return int
     */
    public function seedChannels(string $owner, array $channels): int
    {
        $count = 0;
        foreach ($channels as $channel) {
            $existing = $this->channelDao->getByChannelKey($channel['channel_key']);
            if ($existing) {
                $existingOwner = (string)($existing['owner_plugin_id'] ?? '');
                if ($existingOwner === '') {
                    if ($this->channelDao->claimUnowned((int)$existing['id'], $owner)) {
                        $count++;
                    } else {
                        $latest = $this->channelDao->getByChannelKey($channel['channel_key']);
                        if ((string)($latest['owner_plugin_id'] ?? '') !== $owner) {
                            throw new \RuntimeException("通知渠道 {$channel['channel_key']} 归属发生变更");
                        }
                    }
                } elseif ($existingOwner !== $owner) {
                    throw new \RuntimeException("通知渠道 {$channel['channel_key']} 已归属其他插件");
                }
                $update = [];
                foreach (['channel_name', 'icon', 'description', 'adapter_class'] as $field) {
                    if (empty($existing[$field]) && !empty($channel[$field])) {
                        $update[$field] = $channel[$field];
                    }
                }
                if ($update) {
                    $update['updated_at'] = time();
                    $this->channelDao->update((int)$existing['id'], $update);
                    $count++;
                }
                continue;
            }
            $this->channelDao->save(array_merge([
                'support_user' => 1, 'support_platform' => 0, 'status' => 1, 'sort' => 100,
                'created_at' => time(), 'updated_at' => time(), 'owner_plugin_id' => $owner,
            ], $channel));
            $count++;
        }
        return $count;
    }

    /**
     * 仅删除自有配置及模板；被他人模板引用的渠道定义保留。
     * @param string $owner canonical 插件 ID
     * @return int
     */
    public function clean(string $owner): int
    {
        $count = 0;
        foreach ($this->configDao->getList(['owner_plugin_id' => $owner]) as $record) {
            $count += $this->templateDao->deleteByNotificationId((int)$record['id']);
            $count += $this->configDao->deleteOwned((int)$record['id'], $owner);
        }
        foreach ($this->channelDao->getList(['owner_plugin_id' => $owner]) as $channel) {
            if ($this->templateDao->countByChannelKey($channel['channel_key']) === 0) {
                $count += $this->channelDao->deleteOwned((int)$channel['id'], $owner);
            }
        }
        if ($count) {
            app()->make(SendNoticeService::class)->clearCache();
        }
        return $count;
    }
}
