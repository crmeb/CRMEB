<?php
declare(strict_types=1);

namespace app\dao\order;

use crmeb\basic\BaseDao;
use app\model\order\OrderCenter;
use app\model\user\User;

/**
 * 订单中台数据访问层
 * 提供订单中台表的基础数据库操作方法
 */
class OrderCenterDao extends BaseDao
{
    /**
     * 设置模型类名
     * @return string 返回模型类名
     */
    protected function setModel(): string
    {
        return OrderCenter::class;
    }

    public function getByIdForUpdate(int $id)
    {
        return $this->getModel()->where('id', $id)->lock(true)->find();
    }

    /**
     * 按业务单号锁定中台订单。
     * @param string $orderNo 订单号
     * @return mixed
     */
    public function getByOrderNoForUpdate(string $orderNo)
    {
        return $this->getModel()->where('order_no', $orderNo)->lock(true)->find();
    }

    /**
     * 仅在订单仍未支付且扩展信息未变化时保存支付信息。
     * @param int $id 订单ID
     * @param array|string|null $previousExtra 原扩展信息（兼容 JSON 自动转换后的数组）
     * @param string $extra 更新后的扩展信息
     * @return bool
     */
    public function updateUnpaidPaymentContext(int $id, array|string|null $previousExtra, string $extra): bool
    {
        // JSON 字段可能被 ORM 解码为数组，绑定 SQL 前转换回 JSON，保留原值并发校验。
        if (is_array($previousExtra)) {
            $previousExtra = json_encode($previousExtra, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        return $this->getModel()->where('id', $id)->where('pay_status', 0)
            ->whereRaw('extra_info <=> CAST(? AS JSON)', [$previousExtra])->update(['extra_info' => $extra]) === 1;
    }

    /**
     * 获取分页列表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string $order 排序方式
     * @param array $with 关联预加载
     * @param bool $search 是否使用搜索器
     * @return array 返回分页数据
     */
    public function selectList(array $where = [], string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
    {
        return $this->getModel()->where($where)->field($field)->order($order)->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取记录总数
     * @param array $where 查询条件
     * @param bool $search 是否使用搜索器
     * @return int 返回记录数
     */
    public function count(array $where = [], bool $search = true): int
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 用户订单范围查询：本人下单的订单 + 本人领取的礼品订单
     * 两个条件均为索引字段（idx_uid / idx_gift_uid），支持索引合并
     * @param int $uid 用户ID
     * @return mixed 返回查询对象
     */
    public function userOrderQuery(int $uid)
    {
        return $this->getModel()->whereRaw(
            '(uid = :owner_uid OR gift_uid = :gift_uid)',
            ['owner_uid' => $uid, 'gift_uid' => $uid]
        );
    }

    /**
     * 保存或更新记录
     * 根据条件判断是新增还是更新，支持幂等性操作
     * 兜底机制：若按 order_no 找不到，再按 (source_type, source_id) 查找，
     * 存在相同组合（stale record，来自旧代码用 store_order.id 作 source_id 或之前失败订单）则复用更新，
     * 避免 uk_source_order 唯一键冲突。
     * @param array $where 查询条件
     * @param array $data 数据
     * @return bool 返回操作结果
     */
    public function saveOrUpdate(array $where, array $data): bool
    {
        // 1. 优先按 order_no 查找
        $record = $this->getModel()->where($where)->find();
        if ($record) {
            $result = $record->save($data);
            \think\facade\Log::info('[OrderCenterDao] update by order_no, order_no=' . ($where['order_no'] ?? '') . ', result=' . json_encode($result));
            return (bool)$result;
        }

        // 2. 按 (source_type, source_id) 兜底查找 — 旧代码遗留的 stale record
        $sourceType = $data['source_type'] ?? '';
        $sourceId = $data['source_id'] ?? 0;
        if ($sourceType && $sourceId) {
            $stale = $this->getModel()
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->find();
            if ($stale) {
                \think\facade\Log::warning('[OrderCenterDao] found stale center record, source_type=' . $sourceType . ', source_id=' . $sourceId . ', old_order_no=' . ($stale['order_no'] ?? '') . ', new_order_no=' . ($where['order_no'] ?? ''));
                $result = $stale->save(array_merge($where, $data));
                return (bool)$result;
            }
        }

        // 3. 真正的新增
        \think\facade\Log::info('[OrderCenterDao] create order_center, order_no=' . ($where['order_no'] ?? '') . ', data_keys=' . implode(',', array_keys($data)));
        $model = $this->getModel()::create(array_merge($where, $data));
        $insertId = $model->id ?? 0;
        \think\facade\Log::info('[OrderCenterDao] create result, insert_id=' . $insertId);
        return (bool)$insertId;
    }

    /**
     * 更新记录
     * @param int|string|array $id 主键ID或条件数组
     * @param array $data 更新数据
     * @param string|null $key 主键字段名
     * @return mixed 返回操作结果
     */
    public function update($id, array $data, ?string $key = null)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$key ?? $this->getPk() => $id];
        }
        return $this->getModel()->where($where)->update($data);
    }

    /**
     * 获取单条记录
     * @param int|string|array $id 主键ID或条件数组
     * @param array|null $field 查询字段
     * @param array|null $with 关联预加载
     * @return mixed 返回记录对象或null
     */
    public function get($id, ?array $field = [], ?array $with = [])
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$this->getPk() => $id];
        }
        // 保持 BaseDao::get 的字段/关联契约；物流等窄查询不得无意读取整行订单数据。
        return $this->getModel()->where($where)
            ->when(count($with), function ($query) use ($with) {
                $query->with($with);
            })
            ->field($field ?? ['*'])
            ->find();
    }

    /**
     * 获取数据库查询对象
     * 用于复杂查询场景
     * @return mixed 返回数据库查询对象
     */
    public function getDb()
    {
        return $this->getModel();
    }

    /**
     * 获取订单总数量
     * @param array $where 查询条件
     * @return int 返回订单总数
     */
    public function storeOrderCount(array $where = []): int
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 根据条件获取字段求和
     * @param array $where 查询条件
     * @param string $field 求和字段
     * @return float 返回求和结果
     */
    public function getWhereSumField(array $where, string $field)
    {
        return $this->buildWhereQuery($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->sum($field);
    }

    /**
     * 根据条件分组查询
     * @param array $where 查询条件
     * @param string $field 求和字段
     * @param string $group 分组字段
     * @return mixed 返回分组结果
     */
    public function getGroupField(array $where, string $field, string $group)
    {
        return $this->buildWhereQuery($where)
            ->when(isset($where['timeKey']), function ($query) use ($where, $field, $group) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
                $timeUinx = "%H";
                if ($where['timeKey']['days'] == 1) {
                    $timeUinx = "%H";
                } elseif ($where['timeKey']['days'] == 30) {
                    $timeUinx = "%Y-%m-%d";
                } elseif ($where['timeKey']['days'] == 365) {
                    $timeUinx = "%Y-%m";
                } elseif ($where['timeKey']['days'] > 1 && $where['timeKey']['days'] < 30) {
                    $timeUinx = "%Y-%m-%d";
                } elseif ($where['timeKey']['days'] > 30 && $where['timeKey']['days'] < 365) {
                    $timeUinx = "%Y-%m";
                }
                $query->field("sum($field) as number,FROM_UNIXTIME($group, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME($group, '$timeUinx')");
            })
            ->order('pay_time ASC')->select()->toArray();
    }

    /**
     * 统计指定时间范围内的订单数
     * @param array $where 查询条件（含 timeKey 时间范围，其余条件为简单相等）
     * @return int 订单数
     */
    public function getOrderCountByWhere(array $where): int
    {
        return $this->buildWhereQuery($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->count();
    }

    /**
     * 统计指定时间范围内的支付人数（去重 uid）
     * @param array $where 查询条件（含 timeKey 时间范围，其余条件为简单相等）
     * @return int 去重支付人数
     */
    public function getPayPeopleCount(array $where): int
    {
        return $this->buildWhereQuery($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->group('uid')
            ->count();
    }

    /**
     * 构建聚合查询基础条件
     * 中台聚合方法不依赖模型搜索器：遍历 where 将简单相等/三元组条件直接应用，
     * timeKey 由各聚合方法内部单独处理（whereBetweenTime），避免 search($where, false) 静默丢弃非搜索器条件
     * @param array $where 查询条件
     * @return \think\db\Query
     */
    private function buildWhereQuery(array $where)
    {
        $query = $this->getModel();
        foreach ($where as $key => $value) {
            // timeKey 由调用方聚合逻辑单独处理，跳过
            if (in_array($key, ['timeKey', 'store_stock', 'integral_time'], true)) {
                continue;
            }
            if (is_array($value) && count($value) === 3) {
                // 三元组条件：['field', 'op', 'value']，链式赋回保证条件累加
                $query = $query->where($value[0], $value[1], $value[2]);
            } else {
                $query = $query->where($key, $value);
            }
        }
        return $query;
    }

    /**
     * 按下单用户集合获取推广订单分页列表（仅已支付且未退款）
     * 新架构订单表无推广关系快照字段，推广关系由调用方按用户表实时解析后传入 uid 集合
     * @param array $uids 下单用户ID集合
     * @param array $where 筛选条件（data: [起, 止] 日期；order_id: 订单号模糊搜索）
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array ['list' => 列表, 'count' => 总数]
     */
    public function getSpreadOrderPageByUids(array $uids, array $where = [], int $page = 0, int $limit = 0): array
    {
        // 无下单用户集合时直接返回空结构，避免无效查询
        if (empty($uids)) {
            return ['list' => [], 'count' => 0];
        }
        $model = $this->getModel()
            ->where('uid', 'IN', $uids)
            ->where('pay_status', 1)
            ->where('refund_status', 0)
            ->when(!empty($where['data']), function ($query) use ($where) {
                // 按下单时间范围筛选（data 为 [开始日期, 结束日期]）
                $query->whereBetween('add_time', [strtotime($where['data'][0]), strtotime($where['data'][1] . ' 23:59:59')]);
            })
            ->when(!empty($where['order_id']), function ($query) use ($where) {
                // 订单号模糊搜索
                $query->whereLike('order_no', '%' . $where['order_id'] . '%');
            });
        $count = $model->count();
        $list = $model->order('add_time desc')->page($page, $limit)->select()->toArray();
        return compact('list', 'count');
    }

    /**
     * 按下单人的归属字段分组统计订单（事业部/代理商/员工维度统计）
     * 新架构订单表无 division_id/agent_id/staff_id 快照字段，归属关系实时取自用户表
     * @param string $userField 用户表分组字段（division_id/agent_id/staff_id，白名单校验）
     * @param array $time 时间范围 [开始日期, 结束日期]
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string $sort 排序字段（为空时默认按订单数倒序）
     * @param string $order 排序方向
     * @return array ['list' => 列表, 'count' => 总数]
     */
    public function statisticGroupByUserField(string $userField, array $time = [], int $page = 0, int $limit = 0, string $sort = '', string $order = ''): array
    {
        // 分组字段白名单校验，防止 SQL 注入
        if (!in_array($userField, ['division_id', 'agent_id', 'staff_id'], true)) {
            $userField = 'division_id';
        }
        // 取用户表真实表名（含前缀），拼接关联查询
        $userTable = app()->make(User::class)->getName();
        $model = $this->getModel()->alias('o')
            ->join($userTable . ' u', 'o.uid = u.uid')
            ->where('u.' . $userField, '>', 0)
            ->when(!empty($time), function ($query) use ($time) {
                // 按下单时间范围筛选
                $query->whereBetween('o.add_time', [strtotime($time[0]), strtotime($time[1] . ' 23:59:59')]);
            });
        // 分组后总数：先取全部分组键再计数（分组维度为事业部/代理商/员工，数量有限）
        $count = count($model->distinct(true)->field('u.' . $userField)->select()->toArray());
        // 默认按订单数倒序
        $orderStr = $sort === '' ? 'order_sum desc' : $sort . ' ' . $order;
        $list = $model->fieldRaw('u.' . $userField . ', COUNT(*) AS order_sum, SUM(o.pay_price) AS order_sum_price')
            ->group('u.' . $userField)->order($orderStr)->page($page, $limit)->select()->toArray();
        return compact('list', 'count');
    }

    /**
     * 统计用户集合的已支付未退款订单金额与数量（推广订单概况）
     * @param array $uids 用户ID集合
     * @return array ['order_price' => 订单金额合计, 'order_count' => 订单数量]
     */
    public function getSpreadOrderStatByUids(array $uids): array
    {
        // 无用户集合时直接返回零值结构
        if (empty($uids)) {
            return ['order_price' => 0, 'order_count' => 0];
        }
        $res = $this->getModel()
            ->where('uid', 'IN', $uids)
            ->where('pay_status', 1)
            ->where('refund_status', 0)
            ->fieldRaw('IFNULL(SUM(pay_price),0) AS order_price, COUNT(*) AS order_count')
            ->find();
        return $res ? $res->toArray() : ['order_price' => 0, 'order_count' => 0];
    }

    /**
     * 订单图表分组统计
     * 按周期对已支付订单进行分组，统计每天的订单量与销售额
     * @param int $startTime 开始时间戳
     * @param int $endTime 结束时间戳
     * @param string $cycle 统计周期：thirtyday=按天、month=按月、其余=按年
     * @return array 返回分组统计列表（day/count/price）
     */
    public function orderCharts(int $startTime, int $endTime, string $cycle = 'thirtyday'): array
    {
        // 根据统计周期决定分组粒度
        switch ($cycle) {
            case 'thirtyday':
                $format = '%Y-%m-%d';
                break;
            case 'month':
                $format = '%Y-%m';
                break;
            default:
                $format = '%Y';
                break;
        }

        return $this->getModel()
            ->where('is_del', 0)
            ->where('pay_status', 1)
            ->whereBetween('pay_time', [$startTime, $endTime])
            ->fieldRaw("FROM_UNIXTIME(pay_time, '{$format}') as day, COUNT(*) as count, COALESCE(SUM(pay_price), 0) as price")
            ->group('day')
            ->order('day', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取订单列表（导出用）
     * 兼容旧 getOrderList 调用方，适配新订单中台字段：
     * - 旧字段 status → 新表 status_code
     * - 旧字段 pid/shipping_type 在新表不存在，直接忽略
     * - 解析 extra_info 快照，组装联系人/地址/商品明细（_info）/表单信息
     * @param array $where 查询条件
     * @param array $field 查询字段
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param array $with 兼容旧调用方的关联参数（订单中台暂不使用）
     * @return array 返回 ['data' => 订单列表, 'count' => 订单总数]
     */
    public function getOrderList(array $where = [], array $field = ['*'], int $page = 0, int $limit = 0, array $with = []): array
    {
        $query = $this->getModel();
        // 旧字段名映射到新表统一字段
        $fieldMap = [
            'status'        => 'status_code',
            'pay_status'    => 'pay_status',
            'refund_status' => 'refund_status',
            'product_type'  => 'product_type',
            'order_no'      => 'order_no',
            'uid'           => 'uid',
            'pay_type'      => 'pay_type',
        ];
        foreach ($fieldMap as $old => $new) {
            if (isset($where[$old]) && $where[$old] !== '' && $where[$old] !== null) {
                $query->where($new, $where[$old]);
                unset($where[$old]);
            }
        }
        // 新表不存在的旧字段（pid/shipping_type）直接忽略
        unset($where['pid'], $where['shipping_type']);
        // 日期范围筛选
        if (!empty($where['date_range'])) {
            [$start, $end] = explode('-', (string)$where['date_range']);
            $query->whereBetween('add_time', [strtotime(trim($start)), strtotime(trim($end)) + 86399]);
            unset($where['date_range']);
        }
        // 剩余条件按简单相等处理
        foreach ($where as $key => $value) {
            if ($value === '' || $value === null) continue;
            $query->where($key, $value);
        }
        $count = $query->count();
        $query->field($field)->order('id desc');
        if ($page > 0 && $limit > 0) {
            $query->page($page, $limit);
        }
        $list = $query->select()->toArray();
        // 解析 extra_info 快照，组装导出所需字段
        foreach ($list as &$item) {
            $extra = is_string($item['extra_info'] ?? null) ? (json_decode($item['extra_info'], true) ?: []) : ($item['extra_info'] ?? []);
            $item['extra_info'] = $extra;
            $contact = $extra['contact'] ?? [];
            $item['real_name'] = $contact['name'] ?? '';
            $item['user_phone'] = $contact['phone'] ?? '';
            $addr = $contact['address'] ?? '';
            $item['user_address'] = is_array($addr)
                ? implode('', array_filter([$addr['province'] ?? '', $addr['city'] ?? '', $addr['district'] ?? '', $addr['detail'] ?? '']))
                : ($addr ?: '');
            $item['custom_form'] = $extra['custom_form'] ?? [];
            // 商品明细组装为旧 _info（cart_info）结构，供导出遍历
            $info = [];
            foreach ($extra['items'] ?? [] as $it) {
                $info[] = [
                    'cart_info' => [
                        'productInfo' => [
                            'store_name' => $it['store_name'] ?? '',
                            'attrInfo'   => $it['attr_info'] ?? [],
                        ],
                        'cart_num'  => $it['cart_num'] ?? 1,
                        'truePrice' => $it['true_price'] ?? ($it['price'] ?? '0.00'),
                    ],
                ];
            }
            $item['_info'] = $info;
        }
        return ['data' => $list, 'count' => $count];
    }

    /**
     * 按订单号批量获取普通商品订单的快递信息（发货单导出用）
     * @param array $orderNos 订单号集合
     * @return array 返回订单号 => 快递信息映射
     */
    public function getDeliveryInfoByOrderNos(array $orderNos): array
    {
        if (empty($orderNos)) {
            return [];
        }
        $list = \think\facade\Db::name('pt_normal_order')
            ->whereIn('order_no', $orderNos)
            ->field('order_no, delivery_name, delivery_id, delivery_type')
            ->select()
            ->toArray();
        return array_column($list, null, 'order_no');
    }
}
