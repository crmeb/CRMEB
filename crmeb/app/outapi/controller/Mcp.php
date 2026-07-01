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
namespace app\outapi\controller;

use think\facade\Db;
use think\Request;

/**
 * MCP (Model Context Protocol) 控制器
 * 提供 AI 助手调用 CRMEB API 的标准接口
 *
 * 认证方式：account + password
 * 通过请求头传递账号和密码进行认证
 */
class Mcp extends AuthController
{
    /**
     * 初始化
     * MCP 接口不走 Token 中间件，直接使用 appid + appsecret 认证
     */
    protected function initialize()
    {
        // 不调用父类 initialize，因为 MCP 不走 Token 中间件
        $this->authByAppSecret();
    }

    /**
     * 通过 appid + appsecret 认证
     * 参考 AuthTokenMiddleware 的验证流程
     */
    private function authByAppSecret()
    {
        $account = $this->request->header('account', '');
        $password = $this->request->header('password', '');

        if (empty($account) || empty($password)) {
            $this->authFail('认证失败：缺少 account 或 password');
            return;
        }

        try {
            // 查询账号信息
            $accountInfo = Db::name('out_account')
                ->where('appid', $account)
                ->where('is_del', 0)
                ->find();

            // 账号不存在
            if (!$accountInfo) {
                $this->authFail('账号不存在');
                return;
            }

            // 验证密码
            if (!password_verify($password, $accountInfo['appsecret'])) {
                $this->authFail('密码验证失败');
                return;
            }

            // 检查账号状态（status=0 或 status=2 表示禁用）
            if ($accountInfo['status'] == 0 || $accountInfo['status'] == 2) {
                $this->authFail('账号已被禁用');
                return;
            }

            // 认证成功，设置账号信息
            $this->outId = (int)$accountInfo['id'];
            $this->outInfo = $accountInfo;

            // 验证接口权限（参考 AuthTokenMiddleware）
            $this->verifyAuth();

        } catch (\crmeb\exceptions\AuthException $e) {
            // AuthException 转换为友好错误
            $this->authFail('您暂时没有访问权限');
        } catch (\Exception $e) {
            // 不暴露具体错误信息
            $this->authFail('认证失败');
        }
    }

    /**
     * 验证接口权限
     * 参考 AuthTokenMiddleware 的 verifyAuth 逻辑
     * MCP 接口需要进行路由权限检查
     */
    private function verifyAuth()
    {
        try {
            // 注入 outId 和 outInfo 到 request（模拟中间件的行为）
            $outInfo = $this->outInfo;
            $this->request->macro('outId', function () use (&$outInfo) {
                return (int)$outInfo['id'];
            });
            $this->request->macro('outInfo', function () use (&$outInfo) {
                return $outInfo;
            });

            // 调用接口权限验证服务
            $outInterfaceServices = app()->make(\app\services\out\OutInterfaceServices::class);
            $outInterfaceServices->verifyAuth($this->request);

        } catch (\crmeb\exceptions\AuthException $e) {
            // 权限验证失败，抛出友好的错误信息
            throw new \crmeb\exceptions\AuthException(110000); // 无权限访问
        } catch (\Exception $e) {
            // 其他异常，统一返回无权限
            throw new \crmeb\exceptions\AuthException(110000);
        }
    }

    /**
     * 认证失败处理
     * 设置错误标识，在 index 方法中返回错误响应
     */
    private function authFail(string $message)
    {
        $this->outId = 0;
        $this->outInfo = ['error' => $message];
    }

    /**
     * 获取MCP工具定义列表
     * 定义所有可供AI助手调用的工具及其参数结构
     *
     * @return array 工具定义数组
     */
    private function getTools(): array
    {
        return [
            // 分类管理
            [
                'name' => 'crmeb_category_list',
                'description' => '获取商品分类列表，支持树形结构展示',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'number', 'description' => '页码（非树形模式时有效）'],
                        'limit' => ['type' => 'number', 'description' => '每页数量（非树形模式时有效）'],
                        'tree' => ['type' => 'boolean', 'description' => '是否返回树形结构，默认为true'],
                        'pid' => ['type' => 'number', 'description' => '父级ID，指定则只返回该父级下的分类'],
                    ],
                ],
            ],
            [
                'name' => 'crmeb_category_detail',
                'description' => '获取分类详情',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'number', 'description' => '分类ID'],
                    ],
                    'required' => ['id'],
                ],
            ],

            // 商品管理
            [
                'name' => 'crmeb_product_list',
                'description' => '获取商品列表',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'number', 'description' => '页码'],
                        'limit' => ['type' => 'number', 'description' => '每页数量'],
                        'cate_id' => ['type' => 'number', 'description' => '分类ID'],
                        'keyword' => ['type' => 'string', 'description' => '搜索关键词'],
                        'stock_min' => ['type' => 'number', 'description' => '最小库存'],
                        'stock_max' => ['type' => 'number', 'description' => '最大库存'],
                    ],
                ],
            ],
            [
                'name' => 'crmeb_product_detail',
                'description' => '获取商品详情',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'number', 'description' => '商品ID'],
                    ],
                    'required' => ['id'],
                ],
            ],

            // 订单管理
            [
                'name' => 'crmeb_order_list',
                'description' => '获取订单列表',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'number', 'description' => '页码'],
                        'limit' => ['type' => 'number', 'description' => '每页数量'],
                        'status' => ['type' => 'number', 'description' => '订单状态'],
                        'keyword' => ['type' => 'string', 'description' => '搜索关键词'],
                    ],
                ],
            ],
            [
                'name' => 'crmeb_order_detail',
                'description' => '获取订单详情',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'order_id' => ['type' => 'string', 'description' => '订单号'],
                    ],
                    'required' => ['order_id'],
                ],
            ],
            [
                'name' => 'crmeb_order_express_list',
                'description' => '获取物流公司列表',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ],

            // 售后管理
            [
                'name' => 'crmeb_refund_list',
                'description' => '获取售后订单列表',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'number', 'description' => '页码'],
                        'limit' => ['type' => 'number', 'description' => '每页数量'],
                    ],
                ],
            ],
            [
                'name' => 'crmeb_refund_detail',
                'description' => '获取售后订单详情',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'order_id' => ['type' => 'string', 'description' => '售后订单号'],
                    ],
                    'required' => ['order_id'],
                ],
            ],

            // 优惠券管理
            [
                'name' => 'crmeb_coupon_list',
                'description' => '获取优惠券列表',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'number', 'description' => '页码'],
                        'limit' => ['type' => 'number', 'description' => '每页数量'],
                    ],
                ],
            ],

            // 用户管理
            [
                'name' => 'crmeb_user_list',
                'description' => '获取用户列表',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'number', 'description' => '页码'],
                        'limit' => ['type' => 'number', 'description' => '每页数量'],
                        'keyword' => ['type' => 'string', 'description' => '搜索关键词'],
                    ],
                ],
            ],
            [
                'name' => 'crmeb_user_detail',
                'description' => '获取用户详情',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'uid' => ['type' => 'number', 'description' => '用户ID'],
                    ],
                    'required' => ['uid'],
                ],
            ],
        ];
    }

    /**
     * 处理工具调用
     * 根据工具名称分发到对应的处理方法
     *
     * @param string $name 工具名称
     * @param array $args 工具参数
     * @return array 处理结果
     * @throws \Exception 未知工具或参数错误时抛出异常
     */
    private function handleToolCall(string $name, array $args = [])
    {
        switch ($name) {
            // 分类管理
            case 'crmeb_category_list':
                return $this->categoryList($args);
            case 'crmeb_category_detail':
                if (!isset($args['id']) || !is_numeric($args['id'])) {
                    throw new \Exception('参数错误：缺少 id 或格式不正确');
                }
                return $this->categoryDetail((int)$args['id']);

            // 商品管理
            case 'crmeb_product_list':
                return $this->productList($args);
            case 'crmeb_product_detail':
                if (!isset($args['id']) || !is_numeric($args['id'])) {
                    throw new \Exception('参数错误：缺少 id 或格式不正确');
                }
                return $this->productDetail((int)$args['id']);

            // 订单管理
            case 'crmeb_order_list':
                return $this->orderList($args);
            case 'crmeb_order_detail':
                if (empty($args['order_id'])) {
                    throw new \Exception('参数错误：缺少 order_id');
                }
                return $this->orderDetail($args['order_id']);
            case 'crmeb_order_express_list':
                return $this->orderExpressList();

            // 售后管理
            case 'crmeb_refund_list':
                return $this->refundList($args);
            case 'crmeb_refund_detail':
                if (empty($args['order_id'])) {
                    throw new \Exception('参数错误：缺少 order_id');
                }
                return $this->refundDetail($args['order_id']);

            // 优惠券管理
            case 'crmeb_coupon_list':
                return $this->couponList($args);

            // 用户管理
            case 'crmeb_user_list':
                return $this->userList($args);
            case 'crmeb_user_detail':
                if (!isset($args['uid']) || !is_numeric($args['uid'])) {
                    throw new \Exception('参数错误：缺少 uid 或格式不正确');
                }
                return $this->userDetail((int)$args['uid']);

            default:
                throw new \Exception("未知工具: {$name}");
        }
    }

    // ==================== 分类管理 ====================

    /**
     * 获取商品分类列表
     *
     * @param array $args 查询参数
     *   - page: 页码，默认1（非树形模式时有效）
     *   - limit: 每页数量，默认10，最大100（非树形模式时有效）
     *   - tree: 是否返回树形结构，默认false
     *   - pid: 父级ID，指定则只返回该父级下的分类
     * @return array 分类列表和总数
     */
    private function categoryList(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10))); // 限制最大100
        $isTree = $args['tree'] ?? true;
        $pid = $args['pid'] ?? null;

        // 构建基础查询
        $query = Db::name('store_category')->where('is_show', 1);

        // 如果指定了父级ID
        if ($pid !== null) {
            $query = $query->where('pid', $pid);
        }

        // 树形模式：获取所有分类并构建树
        if ($isTree) {
            $allList = Db::name('store_category')
                ->where('is_show', 1)
                ->order('sort desc, id desc')
                ->select()
                ->toArray();

            // 如果有指定pid，从该节点开始构建树
            if ($pid !== null) {
                $tree = $this->buildCategoryTree($allList, $pid);
                return ['list' => $tree, 'count' => count($tree)];
            }

            // 否则构建完整树（从根节点pid=0开始）
            $tree = $this->buildCategoryTree($allList, 0);
            return ['list' => $tree, 'count' => count($tree)];
        }

        // 普通列表模式
        $list = $query
            ->order('sort desc, id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $query->count();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 构建分类树形结构
     *
     * @param array $list 所有分类数据
     * @param int $pid 父级ID
     * @return array 树形结构
     */
    private function buildCategoryTree(array $list, int $pid): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item['pid'] == $pid) {
                $children = $this->buildCategoryTree($list, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 获取分类详情
     *
     * @param int $id 分类ID
     * @return array 分类详细信息
     * @throws \Exception 分类不存在时抛出异常
     */
    private function categoryDetail(int $id): array
    {
        $info = Db::name('store_category')->where('id', $id)->find();
        if (!$info) {
            throw new \Exception('分类不存在');
        }
        return $info;
    }

    // ==================== 商品管理 ====================

    /**
     * 获取商品列表
     * 支持按分类、关键词、库存范围筛选
     *
     * @param array $args 查询参数
     *   - page: 页码，默认1
     *   - limit: 每页数量，默认10，最大100
     *   - cate_id: 分类ID（可选）
     *   - keyword: 搜索关键词（可选）
     *   - stock_min: 最小库存（可选）
     *   - stock_max: 最大库存（可选）
     * @return array 商品列表和总数
     */
    private function productList(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10))); // 限制最大100

        $where = [['is_show', '=', 1]];

        // 分类筛选：通过关联表查询
        if (!empty($args['cate_id'])) {
            $cateId = (int)$args['cate_id'];
            // 验证分类是否存在
            $categoryExists = Db::name('store_category')->where('id', $cateId)->where('is_show', 1)->count();
            if (!$categoryExists) {
                throw new \Exception('分类不存在');
            }
            // 通过关联表查询商品ID
            $productIds = Db::name('store_product_cate')
                ->where('cate_id', $cateId)
                ->column('product_id');
            if (empty($productIds)) {
                return ['list' => [], 'count' => 0];
            }
            $where[] = ['id', 'in', $productIds];
        }

        // 关键词搜索：转义通配符防止注入
        if (!empty($args['keyword'])) {
            $keyword = addcslashes($args['keyword'], '%_');
            $where[] = ['store_name', 'like', '%' . $keyword . '%'];
        }

        if (isset($args['stock_min'])) {
            $where[] = ['stock', '>=', (int)$args['stock_min']];
        }
        if (isset($args['stock_max'])) {
            $where[] = ['stock', '<=', (int)$args['stock_max']];
        }

        $list = Db::name('store_product')
            ->where($where)
            ->field('id,store_name,cate_id,price,stock,image,sales,is_show')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = Db::name('store_product')->where($where)->count();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 获取商品详情
     *
     * @param int $id 商品ID
     * @return array 商品详细信息（已过滤敏感字段）
     * @throws \Exception 商品不存在时抛出异常
     */
    private function productDetail(int $id): array
    {
        $info = Db::name('store_product')->where('id', $id)->find();
        if (!$info) {
            throw new \Exception('商品不存在');
        }

        // 过滤敏感字段，只返回必要信息
        return [
            'id' => $info['id'],
            'store_name' => $info['store_name'] ?? '',
            'cate_id' => $info['cate_id'] ?? 0,
            'price' => $info['price'] ?? 0,
            'stock' => $info['stock'] ?? 0,
            'image' => $info['image'] ?? '',
            'slider_image' => $info['slider_image'] ?? '',
            'sales' => $info['sales'] ?? 0,
            'unit_name' => $info['unit_name'] ?? '',
            'content' => $info['content'] ?? '',
            'is_show' => $info['is_show'] ?? 1,
        ];
    }

    // ==================== 订单管理 ====================

    /**
     * 获取订单列表
     * 支持按状态和关键词筛选
     *
     * @param array $args 查询参数
     *   - page: 页码，默认1
     *   - limit: 每页数量，默认10，最大100
     *   - status: 订单状态（可选）
     *   - keyword: 搜索关键词，匹配订单号/姓名/手机号（可选）
     * @return array 订单列表和总数
     */
    private function orderList(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10))); // 限制最大100

        $where = [['is_del', '=', 0]];
        if (isset($args['status'])) {
            $where[] = ['status', '=', (int)$args['status']];
        }
        // 关键词搜索：转义通配符防止注入
        if (!empty($args['keyword'])) {
            $keyword = addcslashes($args['keyword'], '%_');
            $where[] = ['order_id|real_name|user_phone', 'like', '%' . $keyword . '%'];
        }

        $list = Db::name('store_order')
            ->where($where)
            ->field('id,order_id,uid,total_price,pay_price,paid,status,delivery_type,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = Db::name('store_order')->where($where)->count();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 获取订单详情
     *
     * @param string $orderId 订单号
     * @return array 订单详细信息（已过滤敏感字段）
     * @throws \Exception 订单不存在时抛出异常
     */
    private function orderDetail(string $orderId): array
    {
        $info = Db::name('store_order')->where('order_id', $orderId)->find();
        if (!$info) {
            throw new \Exception('订单不存在');
        }

        // 过滤敏感字段，只返回必要信息
        return [
            'id' => $info['id'],
            'order_id' => $info['order_id'],
            'uid' => $info['uid'],
            'real_name' => $info['real_name'] ?? '',
            'user_phone' => $info['user_phone'] ?? '',
            'user_address' => $info['user_address'] ?? '',
            'total_price' => $info['total_price'] ?? 0,
            'pay_price' => $info['pay_price'] ?? 0,
            'pay_type' => $info['pay_type'] ?? '',
            'paid' => $info['paid'] ?? 0,
            'status' => $info['status'] ?? 0,
            'delivery_type' => $info['delivery_type'] ?? '',
            'delivery_name' => $info['delivery_name'] ?? '',
            'delivery_id' => $info['delivery_id'] ?? '',
            'refund_status' => $info['refund_status'] ?? 0,
            'add_time' => $info['add_time'] ?? 0,
        ];
    }

    /**
     * 获取物流公司列表
     * 返回所有启用的快递公司信息
     *
     * @return array 物流公司列表
     */
    private function orderExpressList(): array
    {
        $list = Db::name('express')->where('is_show', 1)->field('id,name,code')->select()->toArray();
        return ['list' => $list];
    }

    // ==================== 售后管理 ====================

    /**
     * 获取售后订单列表
     * 返回所有有退款状态的订单
     *
     * @param array $args 查询参数
     *   - page: 页码，默认1
     *   - limit: 每页数量，默认10，最大100
     * @return array 售后订单列表和总数
     */
    private function refundList(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10))); // 限制最大100

        $list = Db::name('store_order')
            ->where('refund_status', '>', 0)
            ->field('id,order_id,uid,total_price,pay_price,refund_status,refund_reason')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = Db::name('store_order')->where('refund_status', '>', 0)->count();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 获取售后订单详情
     *
     * @param string $orderId 售后订单号
     * @return array 售后订单详细信息（已过滤敏感字段）
     * @throws \Exception 售后订单不存在时抛出异常
     */
    private function refundDetail(string $orderId): array
    {
        $info = Db::name('store_order')
            ->where('order_id', $orderId)
            ->where('refund_status', '>', 0)
            ->find();
        if (!$info) {
            throw new \Exception('售后订单不存在');
        }

        // 过滤敏感字段，只返回必要信息
        return [
            'id' => $info['id'],
            'order_id' => $info['order_id'],
            'uid' => $info['uid'],
            'total_price' => $info['total_price'] ?? 0,
            'pay_price' => $info['pay_price'] ?? 0,
            'refund_status' => $info['refund_status'] ?? 0,
            'refund_reason' => $info['refund_reason'] ?? '',
            'refund_price' => $info['refund_price'] ?? 0,
            'refund_explain' => $info['refund_explain'] ?? '',
            'refund_img' => $info['refund_img'] ?? '',
            'add_time' => $info['add_time'] ?? 0,
        ];
    }

    // ==================== 优惠券管理 ====================

    /**
     * 获取优惠券列表
     *
     * @param array $args 查询参数
     *   - page: 页码，默认1
     *   - limit: 每页数量，默认10，最大100
     * @return array 优惠券列表和总数
     */
    private function couponList(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10))); // 限制最大100

        $list = Db::name('store_coupon_issue')
            ->where('is_del', 0)
            ->field('id,coupon_title,coupon_price,use_min_price,start_time,end_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = Db::name('store_coupon_issue')->where('is_del', 0)->count();

        return ['list' => $list, 'count' => $count];
    }

    // ==================== 用户管理 ====================

    /**
     * 获取用户列表
     * 支持按昵称或手机号搜索
     *
     * @param array $args 查询参数
     *   - page: 页码，默认1
     *   - limit: 每页数量，默认10，最大100
     *   - keyword: 搜索关键词，匹配昵称/手机号（可选）
     * @return array 用户列表和总数
     */
    private function userList(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10))); // 限制最大100

        $where = [];
        // 关键词搜索：转义通配符防止注入
        if (!empty($args['keyword'])) {
            $keyword = addcslashes($args['keyword'], '%_');
            $where[] = ['nickname|phone', 'like', '%' . $keyword . '%'];
        }

        $list = Db::name('user')
            ->where($where)
            ->field('uid,nickname,avatar,phone,balance,integral,add_time')
            ->order('uid desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = Db::name('user')->where($where)->count();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 获取用户详情
     *
     * @param int $uid 用户ID
     * @return array 用户详细信息（已过滤敏感字段）
     * @throws \Exception 用户不存在时抛出异常
     */
    private function userDetail(int $uid): array
    {
        $info = Db::name('user')->where('uid', $uid)->find();
        if (!$info) {
            throw new \Exception('用户不存在');
        }

        // 过滤敏感字段，只返回必要信息
        return [
            'uid' => $info['uid'],
            'nickname' => $info['nickname'] ?? '',
            'avatar' => $info['avatar'] ?? '',
            'phone' => $info['phone'] ?? '',
            'now_money' => $info['now_money'] ?? 0,
            'integral' => $info['integral'] ?? 0,
            'level' => $info['level'] ?? 0,
            'add_time' => $info['add_time'] ?? 0,
            'last_time' => $info['last_time'] ?? 0,
        ];
    }

    // ==================== MCP 接口 ====================

    /**
     * MCP 服务入口方法
     * 处理所有 MCP 协议请求，包括：
     * - initialize: 初始化连接，返回服务信息和能力
     * - tools/list: 获取可用工具列表
     * - tools/call: 调用指定工具执行操作
     *
     * @param Request $request HTTP请求对象
     * @return \think\response\Json JSON-RPC 2.0 格式响应
     */
    public function index(Request $request)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $id = $data['id'] ?? null;

        if (!$data) {
            return json(['jsonrpc' => '2.0', 'error' => ['code' => -32700, 'message' => 'Parse error'], 'id' => null]);
        }

        // 认证检查
        if (empty($this->outId)) {
            $errorMsg = $this->outInfo['error'] ?? '认证失败';
            return json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32600, 'message' => $errorMsg]
            ]);
        }

        $method = $data['method'] ?? '';
        $params = $data['params'] ?? [];

        try {
            switch ($method) {
                case 'initialize':
                    return json([
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'protocolVersion' => '2024-11-05',
                            'capabilities' => ['tools' => new \stdClass()],
                            'serverInfo' => [
                                'name' => 'crmeb-mcp-server',
                                'version' => '1.0.0',
                            ],
                        ],
                    ]);

                case 'tools/list':
                    return json([
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => ['tools' => $this->getTools()],
                    ]);

                case 'tools/call':
                    $toolName = $params['name'] ?? '';
                    $toolArgs = $params['arguments'] ?? [];

                    $result = $this->handleToolCall($toolName, $toolArgs);

                    return json([
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                                ],
                            ],
                        ],
                    ]);

                default:
                    return json([
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'error' => ['code' => -32601, 'message' => "Method not found: {$method}"],
                    ]);
            }
        } catch (\Exception $e) {
            // 生产环境返回通用错误信息，避免泄露内部细节
            $errorMessage = $e->getMessage();
            // 对于业务异常（如"商品不存在"），返回具体错误
            // 对于系统异常（如SQL错误），返回通用错误
            $safeErrors = ['商品不存在', '订单不存在', '售后订单不存在', '用户不存在', '分类不存在', '父分类不存在',
                          '同级分类下已存在同名分类', '分类名称不能超过50个字符',
                          '参数错误', '未知工具'];
            $isSafeError = false;
            foreach ($safeErrors as $safeError) {
                if (strpos($errorMessage, $safeError) !== false) {
                    $isSafeError = true;
                    break;
                }
            }

            return json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32603, 'message' => $isSafeError ? $errorMessage : '服务器内部错误'],
            ]);
        }
    }
}
