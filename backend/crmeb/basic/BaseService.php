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

namespace crmeb\basic;

use think\App;
use think\facade\Db;
use think\facade\Config;
use think\facade\Route as Url;
use think\Model;

/**
 * 服务基类
 * 所有业务服务类继承此类
 * Class BaseService
 * @package crmeb\basic
 * @method array|Model|null get($id, ?array $field = []) 获取一条数据
 * @method array|Model|null getOne(array $where, ?string $field = '*') 获取一条数据（不走搜素器）
 * @method string|null batchUpdate(array $ids, array $data, ?string $key = null) 批量修改
 * @method float sum(array $where, string $field, bool $search = false) 求和
 * @method mixed update($id, array $data, ?string $field = '') 修改数据
 * @method bool be($map, string $field = '') 查询一条数据是否存在
 * @method mixed value(array $where, string $field) 获取指定条件下的数据
 * @method int count(array $where = []) 读取数据条数
 * @method int getCount(array $where = []) 获取某些条件总数（不走搜素器）
 * @method array getColumn(array $where, string $field, string $key = '') 获取某个字段数组（不走搜素器）
 * @method mixed delete($id, ?string $key = null) 删除
 * @method mixed save(array $data) 保存数据
 * @method mixed saveAll(array $data) 批量保存数据
 * @method Model selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false) 获取列表
 * @method bool bcInc($key, string $incField, string $inc, ?string $keyField = null, int $acc = 2) 高精度加法
 * @method bool bcDec($key, string $decField, string $dec, ?string $keyField = null, int $acc = 2) 高精度 减法
 * @method mixed decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 减库存加销量
 * @method mixed incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 加库存减销量
 */
abstract class BaseService
{
    /**
     * 应用实例
     * @var App
     */
    protected App $app;

    /**
     * 模型注入
     * @var object
     */
    protected $dao;

    /**
     * 构造方法
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->initialize();
    }

    /**
     * 初始化
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 事务开启
     * @return \think\db\Connection
     */
    protected function beginTransaction()
    {
        return Db::startTrans();
    }

    /**
     * 事务提交
     * @return void
     */
    protected function commit(): void
    {
        Db::commit();
    }

    /**
     * 事务回滚
     * @return void
     */
    protected function rollback(): void
    {
        Db::rollback();
    }

    /**
     * 事务处理
     * @param callable $callback
     * @param bool $isTran 是否开启事务
     * @return mixed
     * @throws \Throwable
     */
    public function transaction(callable $callback, bool $isTran = true)
    {
        return $isTran ? Db::transaction($callback) : $callback();
    }

    /**
     * 获取分页配置
     * @param bool $isPage
     * @param bool $isRelieve
     * @return int[]
     */
    public function getPageValue(bool $isPage = true, bool $isRelieve = true)
    {
        $page = $limit = 0;
        if ($isPage) {
            $page = app()->request->param(Config::get('database.page.pageKey', 'page') . '/d', 0);
            $limit = app()->request->param(Config::get('database.page.limitKey', 'limit') . '/d', 0);
        }
        $limitMax = Config::get('database.page.limitMax');
        $defaultLimit = Config::get('database.page.defaultLimit', 10);
        if ($limit > $limitMax && $isRelieve) {
            $limit = $limitMax;
        }
        return [(int)$page, (int)$limit, (int)$defaultLimit];
    }

    /**
     * 获取路由地址
     * @param string $path
     * @param array $params
     * @param bool $suffix
     * @param bool $isDomain
     * @return \think\route\Url
     */
    public function url(string $path, array $params = [], bool $suffix = false, bool $isDomain = false)
    {
        return Url::buildUrl($path, $params)->suffix($suffix)->domain($isDomain)->build();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }

    /**
     * 处理城市数据
     * @param $address
     * @return array
     */
    public function addressHandle($address)
    {
        if ($address) {
            try {
                preg_match('/(.*?(省|自治区|北京市|天津市|上海市|重庆市|澳门特别行政区|香港特别行政区))/', $address, $matches);
                if (count($matches) > 1) {
                    $province = $matches[count($matches) - 2];
                    $address = preg_replace('/(.*?(省|自治区|北京市|天津市|上海市|重庆市|澳门特别行政区|香港特别行政区))/', '', $address, 1);
                }
                preg_match('/(.*?(市|自治州|地区|区划|县))/', $address, $matches);
                if (count($matches) > 1) {
                    $city = $matches[count($matches) - 2];
                    $address = str_replace($city, '', $address);
                }
                preg_match('/(.*?(区|县|镇|乡|街道))/', $address, $matches);
                if (count($matches) > 1) {
                    $area = $matches[count($matches) - 2];
                    $address = str_replace($area, '', $address);
                }
            } catch (\Throwable $e) {
            }
        }
        return [
            'province' => $province ?? '',
            'city' => $city ?? '',
            'district' => $area ?? '',
            "address" => $address
        ];
    }
}
