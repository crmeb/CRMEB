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

namespace app\services\message\admin;

use crmeb\basic\BaseService;
use app\dao\message\NoticeChannelDao;
use app\model\message\NoticeChannel;
use crmeb\exceptions\AdminException;

/**
 * 消息渠道全局配置服务
 * Class NoticeChannelServices
 * @package app\services\message\admin
 */
class NoticeChannelServices extends BaseService
{
    /**
     * @var NoticeChannelDao
     */
    protected $dao;

    /**
     * 构造方法
     * @param NoticeChannelDao $dao
     */
    public function __construct(NoticeChannelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取渠道配置列表
     *
     * 渠道表（eb_notice_channel）是渠道唯一登记处：核心安装 SQL / 插件 Installer
     * 种子写入（含适配器类名 adapter_class），本方法直接读表返回，列表请求不建档。
     *
     * @param array $filter
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getList(array $filter = [], int $page = 1, int $limit = 20): array
    {
        // 从数据库获取配置信息
        $dbList = $this->dao->getList([], '*', 1, 1000);

        $list = [];
        foreach ($dbList as $dbItem) {
            $adapterClass = (string)($dbItem['adapter_class'] ?? '');

            $item = [
                'id'               => (int)($dbItem['id'] ?? 0),
                'channel_key'      => (string)($dbItem['channel_key'] ?? ''),
                'channel_name'     => (string)($dbItem['channel_name'] ?? ''),
                'support_user'     => (int)($dbItem['support_user'] ?? 1),
                'support_platform' => (int)($dbItem['support_platform'] ?? 1),
                'icon'             => (string)($dbItem['icon'] ?? ''),
                'status'           => (int)($dbItem['status'] ?? 0),
                'config'           => !empty($dbItem['config']) ? json_decode($dbItem['config'], true) : [],
                'sort'             => (int)($dbItem['sort'] ?? 999),
                'description'      => (string)($dbItem['description'] ?? ''),
                // 适配器类名及其可用性（供后台诊断渠道是否就绪）
                'adapter_class'    => $adapterClass,
                'adapter_ready'    => $adapterClass !== '' && class_exists($adapterClass),
            ];

            // 应用筛选条件
            if (!empty($filter['status']) && (int)$item['status'] !== (int)$filter['status']) {
                continue;
            }
            if (!empty($filter['channel_name']) && strpos($item['channel_name'], $filter['channel_name']) === false) {
                continue;
            }
            if (!empty($filter['channel_key']) && $item['channel_key'] !== $filter['channel_key']) {
                continue;
            }

            $list[] = $item;
        }

        // 按 sort 排序
        usort($list, fn($a, $b) => $a['sort'] <=> $b['sort']);

        // 分页
        $total = count($list);
        $offset = ($page - 1) * $limit;
        return array_slice($list, $offset, $limit);
    }

    /**
     * 获取用户端渠道列表（support_user=1）
     * @return array
     */
    public function getUserChannelList(): array
    {
        $list = $this->dao->getList(['support_user' => 1], '*', 1, 100);
        foreach ($list as &$item) {
            $item['config'] = !empty($item['config']) ? json_decode($item['config'], true) : [];
        }
        return $list;
    }

    /**
     * 获取平台端渠道列表（support_platform=1）
     * @return array
     */
    public function getPlatformChannelList(): array
    {
        $list = $this->dao->getList(['support_platform' => 1], '*', 1, 100);
        foreach ($list as &$item) {
            $item['config'] = !empty($item['config']) ? json_decode($item['config'], true) : [];
        }
        return $list;
    }

    /**
     * 获取渠道详情
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        $info = $this->dao->get($id);
        if (!$info) {
            return [];
        }
        $info = $info->toArray();
        $info['config'] = !empty($info['config']) ? json_decode($info['config'], true) : [];
        return $info;
    }

    /**
     * 按渠道标识获取配置
     * @param string $channelKey
     * @return array|null
     */
    public function getByChannelKey(string $channelKey): ?array
    {
        return $this->dao->getByChannelKey($channelKey);
    }

    /**
     * 更新渠道配置
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $this->assertVerificationSmsChannelEnabled($id, $data);
        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config']);
        }
        // 支持范围勾选（0/1），统一转 int 落库
        $scopeChanged = false;
        foreach (['support_user', 'support_platform'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (int)$data[$field];
                $scopeChanged = true;
            }
        }
        $data['updated_at'] = time();
        $res = $this->dao->update($id, $data) !== false;
        if ($res && ($scopeChanged || array_key_exists('status', $data))) {
            // 渠道支持范围变更，清除通知配置缓存，使发送管线重新合并 support 标记
            try {
                app()->make(\app\services\message\pipeline\SendNoticeService::class)->clearCache();
            } catch (\Throwable $e) {
            }
        }
        return $res;
    }

    /**
     * 更新渠道状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function updateStatus(int $id, int $status): bool
    {
        $this->assertVerificationSmsChannelEnabled($id, ['status' => $status]);
        $result = $this->dao->update($id, ['status' => $status, 'updated_at' => time()]) !== false;
        if ($result) app()->make(\app\services\message\pipeline\SendNoticeService::class)->clearCache();
        return $result;
    }

    /**
     * 短信全局开关和用户端支持同时影响验证码直发。
     * @param int $id 渠道 ID
     * @param array $data 拟写入的字段
     * @return void
     */
    protected function assertVerificationSmsChannelEnabled(int $id, array $data): void
    {
        if ((int)($data['status'] ?? 1) === 1 && (int)($data['support_user'] ?? 1) === 1) return;
        $channel = $this->dao->get($id);
        if ($channel && $channel['channel_key'] === 'is_sms') {
            throw new AdminException('短信验证码依赖短信渠道，不能关闭或禁用用户端支持');
        }
    }

    /**
     * 获取所有已启用渠道
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->dao->getAllEnabled();
    }

    /**
     * 获取渠道下拉选项
     * @return array
     */
    public function getChannelOptions(): array
    {
        $channels = $this->dao->getAllEnabled();
        return array_map(function ($item) {
            return ['value' => $item['channel_key'], 'label' => $item['channel_name'], 'channel_key' => $item['channel_key'], 'channel_name' => $item['channel_name']];
        }, $channels);
    }

    /**
     * 获取启用渠道列表
     * @param int $type 1=用户端 2=平台端 0=全部
     * @return array
     */
    public function getChannelList(int $type = 0): array
    {
        $where = ['status' => 1];
        if ($type === 1) {
            $where['support_user'] = 1;
        } elseif ($type === 2) {
            $where['support_platform'] = 1;
        }
        return $this->dao->getList($where, '*', 1, 100);
    }
}
