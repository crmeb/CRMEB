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

namespace crmeb\services\schema;

use crmeb\exceptions\AdminException;

/**
 * 插件管理页面提供者基类
 *
 * 为页面类型无关的方法提供默认实现（调用即抛出"当前页面不支持"异常），
 * 插件按页面类型只覆写需要的方法：
 * - 列表页：覆写 config/list（如含增删改再覆写 form/save/action/detail）
 * - 表单页：覆写 config/form/save
 * - 统计页：覆写 config/stats
 */
abstract class BaseSchemaPageProvider implements SchemaPageProvider
{
    /**
     * 列表数据默认实现：列表页必须覆写
     * @param array $where 搜索条件
     * @param int $page 页码
     * @param int $limit 每页条数
     * @param array $order 排序
     * @return array
     */
    public function list(array $where, int $page, int $limit, array $order = []): array
    {
        throw new AdminException('当前页面类型不支持列表查询');
    }

    /**
     * 表单规则默认实现：含表单的页面必须覆写
     * @param int $id 数据主键，0 表示新增
     * @return array
     */
    public function form(int $id = 0): array
    {
        throw new AdminException('当前页面不支持表单');
    }

    /**
     * 表单保存默认实现：含表单的页面必须覆写
     * @param int $id 数据主键，0 表示新增
     * @param array $data 表单数据
     * @return void
     */
    public function save(int $id, array $data): void
    {
        throw new AdminException('当前页面不支持表单保存');
    }

    /**
     * 详情数据默认实现：含详情抽屉的页面必须覆写
     * @param int $id 数据主键
     * @return array
     */
    public function detail(int $id): array
    {
        throw new AdminException('当前页面不支持详情');
    }

    /**
     * 统计数据默认实现：统计页必须覆写
     * @return array
     */
    public function stats(): array
    {
        throw new AdminException('当前页面类型不支持统计查询');
    }

    /**
     * 通用动作默认实现：含行操作/顶部动作的页面必须覆写
     * @param string $action 动作标识
     * @param array $payload 动作参数
     * @return array
     */
    public function action(string $action, array $payload): array
    {
        throw new AdminException('不支持的动作: ' . $action);
    }
}
