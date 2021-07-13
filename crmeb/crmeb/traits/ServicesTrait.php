<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\traits;

use think\Model;

/**
 * Trait ServicesTrait
 * @package crmeb\traits
 * @method array|Model|null get($id, ?array $field = []) 获取一条数据
 * @method array|Model|null getOne(array $where, ?string $field = '*') 获取一条数据（不走搜素器）
 * @method string|null batchUpdate(array $ids, array $data, ?string $key = null) 批量修改
 * @method float sum(array $where, string $field, bool $search = false) 求和
 * @method mixed update($id, array $data, ?string $field) 修改数据
 * @method bool be($map, string $field = '') 查询一条数据是否存在
 * @method mixed value(array $where, string $field) 获取指定条件下的数据
 * @method int count(array $where = []) 读取数据条数
 * @method int getCount(array $where = []) 获取某些条件总数（不走搜素器）
 * @method array getColumn(array $where, string $field, string $key = '') 获取某个字段数组（不走搜素器）
 * @method mixed delete($id, ?string $key = null) 删除
 * @method mixed save(array $data) 保存数据
 * @method mixed saveAll(array $data) 批量保存数据
 * @method bool bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2) 高精度加法
 * @method bool bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2) 高精度 减法
 * @method mixed decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 减库存加销量
 * @method mixed incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 加库存减销量
 */
trait ServicesTrait
{

}
