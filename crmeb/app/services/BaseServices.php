<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services;

use app\services\user\UserServices;
use crmeb\exceptions\ApiException;
use crmeb\utils\JwtAuth;
use think\facade\Db;
use think\facade\Config;
use think\facade\Route as Url;
use think\Model;

/**
 * Class BaseServices
 * @package app\services
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
 * @method bool bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2) 高精度加法
 * @method bool bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2) 高精度 减法
 * @method mixed decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 减库存加销量
 * @method mixed incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 加库存减销量
 */
abstract class BaseServices
{

    /**
     * 模型注入
     * @var object
     */
    protected $dao;

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
     * 数据库事务操作
     * @param callable $closure
     * @param bool $isTran
     * @return mixed
     */
    public function transaction(callable $closure, bool $isTran = true)
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    /**
     * 创建token
     * @param int $id
     * @param $type
     * @param string $pwd
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function createToken(int $id, $type, $pwd = '')
    {
        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        if ($type == 'api' && !app()->make(UserServices::class)->value(['uid' => $id], 'status')) {
            throw new ApiException(410027);
        }
        if ($type == 'api') {
            $user = app()->make(UserServices::class)->get($id);
            $user = $user->toArray();
            //自定义消息-用户登录成功
            $user['last_time'] = date('Y-m-d H:i:s', $user['last_time']);
            $user['time'] = date('Y-m-d H:i:s');
            event('CustomNoticeListener', [$id, $user, 'login_success']);

            //自定义事件-用户登录
            event('CustomEventListener', ['user_login', [
                'uid' => $user['uid'],
                'nickname' => $user['nickname'],
                'phone' => $user['phone'],
                'add_time' => date('Y-m-d H:i:s', $user['add_time']),
                'login_time' => date('Y-m-d H:i:s'),
                'time' => $user['time'],
                'last_time' => $user['last_time'],
                'user_type' => $user['user_type']
            ]]);
        }
        return $jwtAuth->createToken($id, $type, ['pwd' => md5($pwd)]);
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
     * 密码hash加密
     * @param string $password
     * @return false|string|null
     */
    public function passwordHash(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}
