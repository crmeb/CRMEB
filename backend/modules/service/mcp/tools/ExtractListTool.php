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

namespace modules\service\mcp\tools;

use app\dao\user\UserExtractDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：提现申请列表
 *
 * 银行账号、支付宝账号、姓名、微信号等敏感信息脱敏后返回。
 */
class ExtractListTool implements McpToolInterface
{
    /**
     * 提现 DAO
     * @var UserExtractDao
     */
    private UserExtractDao $dao;

    /**
     * 构造函数
     * @param UserExtractDao $dao 提现 DAO
     */
    public function __construct(UserExtractDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_extract_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询用户佣金提现申请列表，支持按状态、提现方式、用户 UID 筛选；'
            . 'status：0 审核中、1 已提现、-1 未通过（未通过原因见 fail_msg）；'
            . 'extract_type：bank 银行卡、alipay 支付宝、weixin 微信、balance 转余额；'
            . 'balance 字段为提现前佣金余额。银行账号、支付宝账号、姓名、微信号已脱敏。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '提现申请列表';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '财务';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'page'         => ['type' => 'number', 'description' => '页码'],
                'limit'        => ['type' => 'number', 'description' => '每页数量'],
                'uid'          => ['type' => 'number', 'description' => '用户 UID'],
                'status'       => ['type' => 'number', 'description' => '状态：0 审核中、1 已提现、-1 未通过'],
                'extract_type' => ['type' => 'string', 'description' => '提现方式：bank/alipay/weixin/balance'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/uid/status/extract_type）
     * @return array 提现申请列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [];
        if (!empty($args['uid'])) {
            $where[] = ['uid', '=', (int)$args['uid']];
        }
        if (isset($args['status']) && $args['status'] !== '') {
            $where[] = ['status', '=', (int)$args['status']];
        }
        if (isset($args['extract_type']) && $args['extract_type'] !== '') {
            $where[] = ['extract_type', '=', (string)$args['extract_type']];
        }

        $list = $this->dao->search($where)
            ->field('id,uid,real_name,extract_type,bank_code,alipay_code,wechat,extract_price,extract_fee,balance,mark,status,fail_msg,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            $item['real_name'] = self::maskName((string)($item['real_name'] ?? ''));
            $item['bank_code'] = self::maskAccount((string)($item['bank_code'] ?? ''));
            $item['alipay_code'] = self::maskAccount((string)($item['alipay_code'] ?? ''));
            $item['wechat'] = self::maskAccount((string)($item['wechat'] ?? ''));
        }
        unset($item);

        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 姓名脱敏（仅保留首字符）
     * @param string $value 姓名
     * @return string
     */
    private static function maskName(string $value): string
    {
        if ($value === '') {
            return '';
        }
        return mb_substr($value, 0, 1) . str_repeat('*', max(0, mb_strlen($value) - 1));
    }

    /**
     * 账号类信息脱敏（保留后 4 位）
     * @param string $value 账号
     * @return string
     */
    private static function maskAccount(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (mb_strlen($value) <= 4) {
            return '******';
        }
        return str_repeat('*', mb_strlen($value) - 4) . mb_substr($value, -4);
    }
}
