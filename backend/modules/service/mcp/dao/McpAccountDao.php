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

namespace modules\service\mcp\dao;

use crmeb\basic\BaseDao;
use modules\service\mcp\model\McpAccount;

/**
 * MCP 服务账号数据访问对象
 */
class McpAccountDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return McpAccount::class;
    }

    /**
     * 按访问密钥获取有效账号（MCP 接口认证用）
     *
     * 返回账号含 status 字段（0停用 1启用），停用判定由调用方处理，
     * 便于区分「密钥错误」与「账号已停用」两种拒绝原因。
     *
     * @param string $appKey 访问密钥（32 位随机 hex）
     * @return array|null 账号关联数组，不存在或已删除返回 null
     */
    public function getActiveByKey(string $appKey): ?array
    {
        if ($appKey === '') {
            return null;
        }
        $account = $this->getModel()
            ->where(['app_key' => $appKey, 'is_del' => 0])
            ->field('id,remark,app_key,is_system,tools,status,add_time')
            ->find();
        return $account ? $account->toArray() : null;
    }
}
