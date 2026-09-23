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

namespace app\services\driver\printer;

use app\dao\driver\printer\PrinterChannelDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\driver\DriverRegistry;
use think\Validate;

/**
 * 打印机渠道管理服务
 * Class PrinterChannelServices
 * @package app\services\driver\printer
 * @method array|object|null get($id, ?array $field = []) 获取一条数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method save(array $data) 保存数据
 * @method delete($id, ?string $key = null) 删除
 * @method value($where, ?string $field = '') 获取指定字段的值
 */
class PrinterChannelServices extends BaseService
{
    /**
     * @var PrinterChannelDao
     */
    protected $dao;

    /**
     * 初始化
     * @param PrinterChannelDao $dao
     */
    public function __construct(PrinterChannelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取打印机列表（含驱动状态）
     * @param array $where
     * @return array
     */
    public function getList(array $where = []): array
    {
        if (array_key_exists('status', $where)) {
            $where['status'] = self::validateInteger($where['status'], 0, 1, '打印机状态无效');
        }
        $where['is_del'] = 0;
        // 设备配置列表沿用非分页数组契约，只读取展示和编辑所需字段。
        $list = $this->dao->selectList($where, 'id,print_name,driver,printer_id,icon,times,print_type,print_content,sort,status,create_time,update_time', 0, 0, 'sort desc,id asc')->toArray();
        $drivers = $this->getDrivers();

        // 为每条记录附加驱动状态
        foreach ($list as &$item) {
            $item['driver_status'] = false;
            $item['driver_info'] = null;
            $driverName = $item['driver'] ?? '';
            if ($driverName) {
                foreach ($drivers as $driver) {
                    if ($driver['name'] === $driverName) {
                        $item['driver_status'] = $driver['exists'];
                        $item['driver_info'] = $driver;
                        break;
                    }
                }
            }
            $item['add_time'] = $item['add_time'] ?? $item['create_time'] ?? 0;
            if (is_numeric($item['add_time'])) {
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            }
            // 兼容 print_content 字段
            if (isset($item['print_content']) && is_string($item['print_content'])) {
                $item['print_content'] = json_decode($item['print_content'], true) ?: [];
            }
        }

        return $list;
    }

    /**
     * 获取单条详情（含当前配置值）
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        return $this->getChannel($id);
    }

    /**
     * 新增打印机
     * @param array $data
     * @return bool
     */
    public function saveChannel(array $data): bool
    {
        if (array_key_exists('print_content', $data) && !is_array($data['print_content'])) {
            throw new AdminException('小票配置必须是对象');
        }
        $data = $this->normalizeChannelData($data);

        // 从驱动注册中心读取配置字段定义（driver 字段存目录名，按 dir_name 匹配）
        $driverInfo = null;
        foreach (DriverRegistry::scan('printer') as $info) {
            if ($info['dir_name'] === $data['driver']) {
                $driverInfo = $info;
                break;
            }
        }
        if (!$driverInfo) {
            throw new AdminException('打印机驱动信息不存在');
        }

        $saveData = [
            'print_name' => $data['print_name'] ?? '',
            'driver' => $data['driver'],
            'printer_id' => $data['printer_id'],
            'icon' => $data['icon'] ?? '',
            'sort' => $data['sort'] ?? 0,
            'status' => $data['status'] ?? 1,
            'config_fields' => [],
            'times' => $data['times'] ?? 1,
            'print_type' => $data['print_type'] ?? 1,
            'print_content' => json_encode($data['print_content'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'is_del' => 0,
        ];

        if ($this->dao->save($saveData) === false) {
            throw new \RuntimeException('保存打印机失败');
        }
        return true;
    }

    /**
     * 更新打印机
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateChannel(int $id, array $data): bool
    {
        $channel = $this->getChannel($id);
        if (array_key_exists('print_content', $data) && !is_array($data['print_content'])) {
            throw new AdminException('小票配置必须是对象');
        }
        if (array_key_exists('driver', $data) && $data['driver'] !== $channel['driver']) {
            throw new AdminException('已添加的打印机不能更换驱动');
        }
        $normalized = $this->normalizeChannelData(array_replace($channel, $data));
        $updateData = array_intersect_key($normalized, array_flip(['print_name', 'printer_id', 'icon', 'sort', 'status', 'times', 'print_type']));
        if (array_key_exists('print_content', $data)) {
            $updateData['print_content'] = json_encode($normalized['print_content'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        if ($this->dao->update(['id' => $id, 'is_del' => 0], $updateData) === false) {
            throw new \RuntimeException('更新打印机失败');
        }

        return true;
    }

    /**
     * 修改状态
     * @param int $id
     * @param mixed $status
     * @return bool
     */
    public function setStatus(int $id, $status): bool
    {
        $this->getChannel($id);
        $status = self::validateInteger($status, 0, 1, '打印机状态无效');
        if ($this->dao->update(['id' => $id, 'is_del' => 0], ['status' => $status]) === false) {
            throw new \RuntimeException('修改打印机状态失败');
        }
        return true;
    }

    /**
     * 删除打印机（软删除）
     * @param int $id
     * @return bool
     */
    public function deleteChannel(int $id): bool
    {
        $this->getChannel($id);
        if ($this->dao->update(['id' => $id, 'is_del' => 0], ['is_del' => 1]) === false) {
            throw new \RuntimeException('删除打印机失败');
        }
        return true;
    }

    /**
     * 批量排序
     * @param array $sortData [{id: 1, sort: 100}, ...]
     * @return bool
     */
    public function setSort(array $sortData): bool
    {
        $validated = [];
        // 先完整校验，避免后面的非法记录导致前面的排序已被修改。
        foreach ($sortData as $item) {
            if (!is_array($item)) {
                throw new AdminException('打印机排序数据无效');
            }
            $id = self::validateInteger($item['id'] ?? null, 1, PHP_INT_MAX, '打印机ID无效');
            $sort = self::validateInteger($item['sort'] ?? null, 0, 9999, '排序值必须是0至9999的整数');
            $this->getChannel($id);
            $validated[] = ['id' => $id, 'sort' => $sort];
        }
        foreach ($validated as $item) {
            if ($this->dao->update(['id' => $item['id'], 'is_del' => 0], ['sort' => $item['sort']]) === false) {
                throw new \RuntimeException('保存打印机排序失败');
            }
        }
        return true;
    }

    /**
     * 获取小票打印内容配置
     * @param int $id
     * @return array
     */
    public function printContent(int $id): array
    {
        $channel = $this->getChannel($id);
        $content = $this->decodePrintContent($channel['print_content'] ?? null);
        $content['site_url'] = (string)sys_config('site_url');
        return $content;
    }

    /**
     * 保存小票打印内容配置
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function savePrintContent(int $id, array $data): bool
    {
        $this->getChannel($id);
        $content = self::normalizePrintContent($data);
        $this->validateCodeSiteUrl($content);
        if ($this->dao->update(['id' => $id, 'is_del' => 0], ['print_content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]) === false) {
            throw new \RuntimeException('保存小票配置失败');
        }
        return true;
    }

    /**
     * 默认小票模板，与管理端初始配置保持一致。
     * @return array
     */
    public static function defaultPrintContent(): array
    {
        return ['header' => 1, 'delivery' => 1, 'buyer_remarks' => 1, 'goods' => [0], 'freight' => 1,
            'preferential' => 1, 'pay' => [0, 1], 'custom' => 0, 'order' => [0, 1], 'code' => 0,
            'code_url' => '', 'show_notice' => 0, 'notice_content' => ''];
    }

    /**
     * 校验并补齐小票模板，忽略接口附加信息。
     * @param array $data
     * @return array
     */
    public static function normalizePrintContent(array $data): array
    {
        if ($data && array_is_list($data)) {
            throw new AdminException('小票配置必须是对象');
        }
        $defaults = self::defaultPrintContent();
        $content = array_replace($defaults, array_intersect_key($data, $defaults));
        foreach (['header', 'delivery', 'buyer_remarks', 'freight', 'preferential', 'custom', 'code', 'show_notice'] as $field) {
            $content[$field] = self::validateInteger($content[$field], 0, 1, '小票开关配置无效');
        }
        foreach (['goods' => 1, 'pay' => 1, 'order' => 3] as $field => $maximum) {
            if (!is_array($content[$field]) || !array_is_list($content[$field]) || count($content[$field]) > 32) {
                throw new AdminException('小票选项配置必须是数组');
            }
            $options = [];
            foreach ($content[$field] as $value) {
                $options[] = self::validateInteger($value, 0, $maximum, '小票选项配置无效');
            }
            $content[$field] = array_values(array_unique($options));
        }
        foreach (['code_url' => 2000, 'notice_content' => 1000] as $field => $length) {
            if (!is_string($content[$field]) || mb_strlen($content[$field]) > $length) {
                throw new AdminException('小票文本配置无效或超过长度限制');
            }
            $content[$field] = trim($content[$field]);
        }
        $url = $content['code_url'];
        if ($url !== '') {
            $parts = parse_url($url);
            $absolute = is_array($parts) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) && !empty($parts['host']);
            $relative = str_starts_with($url, '/') && !str_starts_with($url, '//') && !isset($parts['scheme']);
            if ((!$absolute && !$relative) || preg_match('/[\s<>]/u', $url)) {
                throw new AdminException('二维码链接必须是有效的网址或站内路径');
            }
        } elseif ($content['code'] === 1) {
            throw new AdminException('请选择二维码链接');
        }
        return $content;
    }

    /**
     * 读取未删除的设备，禁止访问和恢复已删除记录。
     * @param int $id
     * @return array
     */
    protected function getChannel(int $id): array
    {
        if ($id <= 0) {
            throw new AdminException('打印机ID无效');
        }
        $channel = $this->dao->get(['id' => $id, 'is_del' => 0], ['id', 'print_name', 'driver', 'printer_id', 'icon', 'times', 'print_type', 'print_content', 'sort', 'status', 'is_del', 'create_time', 'update_time']);
        if (!$channel) {
            throw new AdminException('打印机不存在');
        }
        return $channel->toArray();
    }

    /**
     * 解析保存的模板，空模板使用默认值，损坏模板显式报错。
     * @param mixed $value
     * @return array
     */
    protected function decodePrintContent($value): array
    {
        if ($value === null || $value === '') {
            return self::defaultPrintContent();
        }
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            throw new AdminException('小票配置格式错误，请重新保存');
        }
        return self::normalizePrintContent($value);
    }

    /**
     * 校验设备字段并转换数值，不允许非法值被强制转换为有效值。
     * @param array $data
     * @return array
     */
    protected function normalizeChannelData(array $data): array
    {
        $data += ['print_name' => '', 'driver' => '', 'printer_id' => '', 'icon' => '', 'sort' => 0,
            'status' => 1, 'times' => 1, 'print_type' => 1, 'print_content' => self::defaultPrintContent()];
        foreach (['print_name' => 255, 'driver' => 128, 'printer_id' => 128, 'icon' => 128] as $field => $length) {
            if (!is_string($data[$field]) || mb_strlen(trim($data[$field])) > $length || ($field !== 'icon' && trim($data[$field]) === '')) {
                throw new AdminException('打印机名称、驱动和设备ID不能为空且不能超过长度限制');
            }
            $data[$field] = trim($data[$field]);
        }
        $data['times'] = self::validateInteger($data['times'], 1, 99, '打印联数必须是1至99的整数');
        $data['print_type'] = self::validateInteger($data['print_type'], 1, 2, '打印时机无效');
        $data['status'] = self::validateInteger($data['status'], 0, 1, '打印机状态无效');
        $data['sort'] = self::validateInteger($data['sort'], 0, 9999, '排序值必须是0至9999的整数');
        $data['print_content'] = $this->decodePrintContent($data['print_content']);
        $this->validateCodeSiteUrl($data['print_content']);
        return $data;
    }

    /**
     * 站内二维码依赖配置的站点域名，不允许保存无法生成网址的模板。
     * @param array $content
     * @return void
     */
    protected function validateCodeSiteUrl(array $content): void
    {
        if ($content['code'] !== 1 || !str_starts_with($content['code_url'], '/')) {
            return;
        }
        $parts = parse_url((string)sys_config('site_url'));
        if (!is_array($parts) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || empty($parts['host'])) {
            throw new AdminException('请先配置有效的站点域名再启用站内二维码');
        }
    }

    /**
     * 严格校验整数和范围，允许 HTTP 表单中的整数字符串。
     * @param mixed $value
     * @param int $minimum
     * @param int $maximum
     * @param string $message
     * @return int
     */
    protected static function validateInteger($value, int $minimum, int $maximum, string $message): int
    {
        $validator = new Validate();
        $validator->rule(['value' => [static function ($number) use ($minimum, $maximum, $message) {
            $validType = is_int($number) || (is_string($number) && preg_match('/^\d+$/D', $number));
            $integer = $validType ? filter_var($number, FILTER_VALIDATE_INT) : false;
            return $integer !== false && $integer >= $minimum && $integer <= $maximum ? true : $message;
        }]])->failException(true)->check(['value' => $value]);
        return (int)$value;
    }

    /**
     * 扫描打印机驱动目录，自动读取每个子目录的 plugin.json 元数据
     * 扫描 driver/printer/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * @return array
     */
    public function getDrivers(): array
    {
        // 统一走驱动注册中心（读取 plugin.json 元数据），按目录名重组原契约结构
        $drivers = [];
        /** @var PrinterDriverServices $driverServices */
        $driverServices = app()->make(PrinterDriverServices::class);
        $enabled = array_column($driverServices->getAvailableDrivers(), null, 'driver_key');
        foreach (DriverRegistry::scan('printer') as $info) {
            $name = $info['dir_name'];
            $driverKey = $info['driver_key'] ?? $name;
            if (!isset($enabled[$driverKey])) {
                continue;
            }
            // 设备选择器不需要服务商凭据，禁止透传运行时配置值。
            unset($info['config_fields']);
            $class = $info['namespace'] . '\\' . $info['class_name'];
            $drivers[] = [
                'name'        => $name,
                'namespace'   => $class,
                'class'       => $class,
                'file_path'   => root_path() . 'driver/printer/' . $name . '/Driver.php',
                'exists'      => true, // 目录已在注册中心登记，视为已安装
                'has_info'    => true, // 登记即有 plugin.json 元数据
                'description' => $info['name'],
                'device_id'   => $info['capabilities']['device_id'] ?? ['label' => '打印机ID', 'tip' => '请输入打印机设备ID'],
                'info'        => $info,
            ];
        }

        return $drivers;
    }
}
