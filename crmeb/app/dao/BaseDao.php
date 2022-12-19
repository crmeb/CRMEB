<?php
/**
 * @author: liaofei<136327134@qq.com>
 * @day: 2020/7/6
 */

namespace app\dao;

use crmeb\basic\BaseModel;
use think\helper\Str;
use think\Model;

/**
 * Class BaseDao
 * @package app\dao
 */
abstract class BaseDao
{
    /**
     * 当前表名别名
     * @var string
     */
    protected $alias;

    /**
     * join表别名
     * @var string
     */
    protected $joinAlis;


    /**
     * 获取当前模型
     * @return string
     */
    abstract protected function setModel(): string;

    /**
     * 设置join链表模型
     * @return string
     */
    protected function setJoinModel(): string
    {
    }

    /**
     * 读取数据条数
     * @param array $where
     * @return int
     */
    public function count(array $where = [])
    {
        return $this->search($where)->count();
    }

    /**
     * 获取某些条件数据
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param bool $search
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', bool $search = false)
    {
        if ($search) {
            $model = $this->search($where);
        } else {
            $model = $this->getModel()->where($where);
        }
        return $model->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when($order !== '', function ($query) use ($order) {
            $query->order($order);
        })->select();
    }

    /**
     * 获取某些条件总数
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 获取某些条件去重总数
     * @param array $where
     * @param $field
     * @param bool $search
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDistinctCount(array $where, $field, bool $search = true)
    {
        if ($search) {
            return $this->search($where)->field('COUNT(distinct(' . $field . ')) as count')->select()->toArray()[0]['count'] ?? 0;
        } else {
            return $this->getModel()->where($where)->field('COUNT(distinct(' . $field . ')) as count')->select()->toArray()[0]['count'] ?? 0;
        }
    }

    /**
     * 获取模型
     * @return BaseModel
     */
    protected function getModel()
    {
        return app()->make($this->setModel());
    }

    /**
     * 获取主键
     * @return array|string
     */
    protected function getPk()
    {
        return $this->getModel()->getPk();
    }

    /**
     * 获取一条数据
     * @param $id
     * @param array|null $field
     * @param array|null $with
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get($id, ?array $field = [], ?array $with = [])
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$this->getPk() => $id];
        }
        return $this->getModel()->where($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->field($field ?? ['*'])->find();
    }

    /**
     * 查询一条数据是否存在
     * @param $map
     * @param string $field
     * @return bool 是否存在
     */
    public function be($map, string $field = '')
    {
        if (!is_array($map) && empty($field)) $field = $this->getPk();
        $map = !is_array($map) ? [$field => $map] : $map;
        return 0 < $this->getModel()->where($map)->count();
    }

    /**
     * 根据条件获取一条数据
     * @param array $where
     * @param string|null $field
     * @param array $with
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne(array $where, ?string $field = '*', array $with = [])
    {
        $field = explode(',', $field);
        return $this->get($where, $field, $with);
    }

    /**
     * 获取单个字段值
     * @param array $where
     * @param string|null $field
     * @return mixed
     */
    public function value(array $where, ?string $field = '')
    {
        $pk = $this->getPk();
        return $this->getModel()->where($where)->value($field ?: $pk);
    }

    /**
     * 获取某个字段数组
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getColumn(array $where, string $field, string $key = '')
    {
        return $this->getModel()->where($where)->column($field, $key);
    }

    /**
     * 删除
     * @param int|string|array $id
     * @return mixed
     */
    public function delete($id, ?string $key = null)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()->where($where)->delete();
    }

    /**
     * 更新数据
     * @param int|string|array $id
     * @param array $data
     * @param string|null $key
     * @return BaseModel
     */
    public function update($id, array $data, ?string $key = null)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()::update($data, $where);
    }

    /**
     * 批量更新数据
     * @param array $ids
     * @param array $data
     * @param string|null $key
     * @return BaseModel
     */
    public function batchUpdate(array $ids, array $data, ?string $key = null)
    {
        return $this->getModel()->whereIn(is_null($key) ? $this->getPk() : $key, $ids)->update($data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        return $this->getModel()::create($data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return \think\Collection
     * @throws \Exception
     */
    public function saveAll(array $data)
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 获取某个字段内的值
     * @param $value
     * @param string $filed
     * @param string|null $valueKey
     * @param array|string[] $where
     * @return mixed
     */
    public function getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = [])
    {
        return $this->getModel()->getFieldValue($value, $filed, $valueKey, $where);
    }

    /**
     * 获取搜索器和搜索条件key
     * @param array $withSearch
     * @return array[]
     * @throws \ReflectionException
     */
    private function getSearchData(array $withSearch)
    {
        $with = [];
        $whereKey = [];
        $responses = new \ReflectionClass($this->setModel());
        foreach ($withSearch as $fieldName) {
            $method = 'search' . Str::studly($fieldName) . 'Attr';
            if ($responses->hasMethod($method)) {
                $with[] = $fieldName;
            } else {
                $whereKey[] = $fieldName;
            }
        }
        return [$with, $whereKey];
    }

    /**
     * 根据搜索器获取搜索内容
     * @param array $withSearch
     * @param array|null $data
     * @return BaseModel
     * @throws \ReflectionException
     */
    protected function withSearchSelect(array $withSearch, ?array $data = [])
    {
        [$with] = $this->getSearchData($withSearch);
        return $this->getModel()->withSearch($with, $data);
    }

    /**
     * 搜索
     * @param array $where
     * @return BaseModel
     * @throws \ReflectionException
     */
    protected function search(array $where = [])
    {
        if ($where) {
            return $this->withSearchSelect(array_keys($where), $where);
        } else {
            return $this->getModel();
        }
    }

    /**
     * 求和
     * @param array $where
     * @param string $field
     * @param bool $search
     * @return float
     * @throws \ReflectionException
     */
    public function sum(array $where, string $field, bool $search = false)
    {
        if ($search) {
            return $this->search($where)->sum($field);
        } else {
            return $this->getModel()->where($where)->sum($field);
        }
    }

    /**
     * 高精度加法
     * @param $key
     * @param string $incField
     * @param string $inc
     * @param string|null $keyField
     * @param int $acc
     * @return bool
     */
    public function bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2)
    {
        return $this->bc($key, $incField, $inc, $keyField, 1);
    }

    /**
     * 高精度 减法
     * @param $key
     * @param string $decField
     * @param string $dec
     * @param string|null $keyField
     * @param int $acc
     * @return bool
     */
    public function bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2)
    {
        return $this->bc($key, $decField, $dec, $keyField, 2);
    }

    /**
     * 高精度计算并保存
     * @param $key
     * @param string $incField
     * @param string $inc
     * @param string|null $keyField
     * @param int $type
     * @param int $acc
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bc($key, string $incField, string $inc, string $keyField = null, int $type = 1, int $acc = 2)
    {
        if ($keyField === null) {
            $result = $this->get($key);
        } else {
            $result = $this->getOne([$keyField => $key]);
        }
        if (!$result) return false;
        $new = 0;
        if ($type === 1) {
            $new = bcadd($result[$incField], $inc, $acc);
        } else if ($type === 2) {
            if ($result[$incField] < $inc) return false;
            $new = bcsub($result[$incField], $inc, $acc);
        }
        $result->{$incField} = $new;
        return false !== $result->save();
    }

    /**
     * 减库存加销量
     * @param array $where
     * @param int $num
     * @param string $stock
     * @param string $sales
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
    {
        $isQuota = false;
        if (isset($where['type']) && $where['type']) {
            $isQuota = true;
            if (count($where) == 2) {
                unset($where['type']);
            }
        }
        $field = $isQuota ? 'stock,quota' : 'stock';
        $product = $this->getModel()->where($where)->field($field)->find();
        if ($product) {
            return $this->getModel()->where($where)->when($isQuota, function ($query) use ($num) {
                $query->dec('quota', $num);
            })->dec($stock, $num)->inc($sales, $num)->update();
        }
        return false;
//        $field = $isQuota ? [$stock, $sales, 'quota'] : [$stock, $sales];
//        $info = $this->getModel()->where($where)->field($field)->find();
//        if ($info) {
//            if ($isQuota) {
//                $info->quota = (int)$info->quota - $num;
//            }
//            $info->stock = (int)$info->stock - $num;
//            $info->sales = (int)$info->sales + $num;
//            return $info->save();
//        } else {
//            return false;
//        }
    }

    /**
     * 加库存减销量
     * @param array $where
     * @param int $num
     * @param string $stock
     * @param string $sales
     * @return mixed
     */
    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
    {
        $isQuota = false;
        if (isset($where['type']) && $where['type']) {
            $isQuota = true;
            if (count($where) == 2) {
                unset($where['type']);
            }
        }
        $salesOne = $this->getModel()->where($where)->value($sales);
        if ($salesOne) {
            $salesNum = $num;
            if ($num > $salesOne) {
                $salesNum = $salesOne;
            }
            return $this->getModel()->where($where)->when($isQuota, function ($query) use ($num) {
                $query->inc('quota', $num);
            })->inc($stock, $num)->dec($sales, $salesNum)->update();
        }
        return true;
//        $field = $isQuota ? [$stock, $sales, 'quota'] : [$stock, $sales];
//        $info = $this->getModel()->where($where)->field($field)->find();
//        if ($info) {
//            if ($isQuota) {
//                $info->quota = (int)$info->quota + $num;
//            }
//            $info->stock = (int)$info->stock + $num;
//            if ((int)$info->sales > $num) {
//                $info->sales = (int)$info->sales - $num;
//            } else {
//                $info->sales = 0;
//            }
//            return $info->save();
//        } else {
//            return false;
//        }
    }

    /**
     * 获取条件数据中的某个值的最大值
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getMax(array $where = [], string $field = '')
    {
        return $this->getModel()->where($where)->max($field);
    }

    /**
     * 获取条件数据中的某个值的最小值
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getMin(array $where = [], string $field = '')
    {
        return $this->getModel()->where($where)->min($field);
    }
}
