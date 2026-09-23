<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\helper\Arr;
use Traversable;

/**
 * 数据集管理类
 *
 * @template TKey of array-key
 * @template-covariant TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Arrayable, Jsonable
{
    /**
     * 数据集数据
     * @var array
     */
    protected $items = [];

    /**
     * 构造函数
     * @param iterable<TKey, TValue>|Collection<TKey, TValue> $items 数据
     */
    public function __construct($items = [])
    {
        $this->items = $this->convertToArray($items);
    }

    /**
     * @param iterable<TKey, TValue>|Collection<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * 是否为空
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * 合并数组
     *
     * @param mixed $items 数据
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->convertToArray($items)));
    }

    /**
     * 按指定键整理数据
     *
     * @param mixed  $items    数据
     * @param string|null $indexKey 键名
     * @return array
     */
    public function dictionary($items = null, ?string &$indexKey = null)
    {
        if ($items instanceof self) {
            $items = $items->all();
        }

        $items = is_null($items) ? $this->items : $items;

        if ($items && empty($indexKey)) {
            $indexKey = is_array($items[0]) ? 'id' : $items[0]->getPk();
        }

        if (isset($indexKey) && is_string($indexKey)) {
            return array_column($items, null, $indexKey);
        }

        return $items;
    }

    /**
     * 比较数组，返回差集
     *
     * @param mixed  $items    数据
     * @param string|null $indexKey 指定比较的键名
     * @return static<TKey, TValue>
     */
    public function diff($items, ?string $indexKey = null)
    {
        if ($this->isEmpty() || is_scalar($this->items[0])) {
            return new static(array_diff($this->items, $this->convertToArray($items)));
        }

        $diff       = [];
        $dictionary = $this->dictionary($items, $indexKey);

        if (is_string($indexKey)) {
            foreach ($this->items as $item) {
                if (!isset($dictionary[$item[$indexKey]])) {
                    $diff[] = $item;
                }
            }
        }

        return new static($diff);
    }

    /**
     * 比较数组，返回交集
     *
     * @param mixed  $items    数据
     * @param string|null $indexKey 指定比较的键名
     * @return static<TKey, TValue>
     */
    public function intersect($items, ?string $indexKey = null)
    {
        if ($this->isEmpty() || is_scalar($this->items[0])) {
            return new static(array_intersect($this->items, $this->convertToArray($items)));
        }

        $intersect  = [];
        $dictionary = $this->dictionary($items, $indexKey);

        if (is_string($indexKey)) {
            foreach ($this->items as $item) {
                if (isset($dictionary[$item[$indexKey]])) {
                    $intersect[] = $item;
                }
            }
        }

        return new static($intersect);
    }

    /**
     * 交换数组中的键和值
     *
     * @return static<TValue, TKey>
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * 返回数组中所有的键名
     *
     * @return static<TKey, TValue>
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * 返回数组中所有的值组成的新 Collection 实例
     * @return static<int, TValue>
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * 删除数组的最后一个元素（出栈）
     *
     * @return TValue
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * 通过使用用户自定义函数，以字符串返回数组
     *
     * @param callable $callback 调用方法
     * @param mixed    $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * 以相反的顺序返回数组。
     *
     * @return static<TKey, TValue>
     */
    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * 删除数组中首个元素，并返回被删除元素的值
     *
     * @return TValue
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * 在数组结尾插入一个元素
     *
     * @param mixed  $value 元素
     * @param string|null $key   KEY
     * @return $this
     */
    public function push($value, ?string $key = null)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * 把一个数组分割为新的数组块.
     *
     * @param int  $size 块大小
     * @param bool $preserveKeys
     * @return static
     */
    public function chunk(int $size, bool $preserveKeys = false)
    {
        $chunks = [];

        foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * 在数组开头插入一个元素
     *
     * @param mixed  $value 元素
     * @param string|null $key   KEY
     * @return $this
     */
    public function unshift($value, ?string $key = null)
    {
        if (is_null($key)) {
            array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }

        return $this;
    }

    /**
     * 给每个元素执行个回调
     *
     *
     * @param callable $callback 回调
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            $result = $callback($item, $key);

            if (false === $result) {
                break;
            } elseif (!is_object($item)) {
                $this->items[$key] = $result;
            }
        }

        return $this;
    }

    /**
     * 用回调函数处理数组中的元素
     *
     * @param callable|null $callback 回调
     * @return static<TKey, TValue>
     */
    public function map(callable $callback)
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * 用回调函数过滤数组中的元素
     *
     * @param callable|null $callback 回调
     * @return static<TKey, TValue>
     */
    public function filter(?callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * 根据字段条件过滤数组中的元素
     *
     * @param string $field    字段名
     * @param mixed  $operator 操作符
     * @param mixed  $value    数据
     * @return static<TKey, TValue>
     */
    public function where(string $field, $operator, $value = null)
    {
        if (is_null($value)) {
            $value    = $operator;
            $operator = '=';
        }

        return $this->filter(function ($data) use ($field, $operator, $value) {
            if (strpos($field, '.')) {
                [$field, $relation] = explode('.', $field);

                $result = $data[$field][$relation] ?? null;
            } else {
                $result = $data[$field] ?? null;
            }

            switch (strtolower($operator)) {
                case '===':
                    return $result === $value;
                case '!==':
                    return $result !== $value;
                case '!=':
                case '<>':
                    return $result != $value;
                case '>':
                    return $result > $value;
                case '>=':
                    return $result >= $value;
                case '<':
                    return $result < $value;
                case '<=':
                    return $result <= $value;
                case 'like':
                    return is_string($result) && false !== strpos($result, $value);
                case 'not like':
                    return is_string($result) && false === strpos($result, $value);
                case 'in':
                    return is_scalar($result) && in_array($result, $value, true);
                case 'not in':
                    return is_scalar($result) && !in_array($result, $value, true);
                case 'between':
                    [$min, $max] = is_string($value) ? explode(',', $value) : $value;
                    return is_scalar($result) && $result >= $min && $result <= $max;
                case 'not between':
                    [$min, $max] = is_string($value) ? explode(',', $value) : $value;
                    return is_scalar($result) && $result > $max || $result < $min;
                case '==':
                case '=':
                default:
                    return $result == $value;
            }
        });
    }

    /**
     * LIKE过滤
     *
     * @param string $field 字段名
     * @param string $value 数据
     * @return static<TKey, TValue>
     */
    public function whereLike(string $field, string $value)
    {
        return $this->where($field, 'like', $value);
    }

    /**
     * NOT LIKE过滤
     *
     * @param string $field 字段名
     * @param string $value 数据
     * @return static<TKey, TValue>
     */
    public function whereNotLike(string $field, string $value)
    {
        return $this->where($field, 'not like', $value);
    }

    /**
     * IN过滤
     *
     * @param string $field 字段名
     * @param array  $value 数据
     * @return static<TKey, TValue>
     */
    public function whereIn(string $field, array $value)
    {
        return $this->where($field, 'in', $value);
    }

    /**
     * NOT IN过滤
     *
     * @param string $field 字段名
     * @param array  $value 数据
     * @return static<TKey, TValue>
     */
    public function whereNotIn(string $field, array $value)
    {
        return $this->where($field, 'not in', $value);
    }

    /**
     * BETWEEN 过滤
     *
     * @param string $field 字段名
     * @param mixed  $value 数据
     * @return static<TKey, TValue>
     */
    public function whereBetween(string $field, $value)
    {
        return $this->where($field, 'between', $value);
    }

    /**
     * NOT BETWEEN 过滤
     *
     * @param string $field 字段名
     * @param mixed  $value 数据
     * @return static<TKey, TValue>
     */
    public function whereNotBetween(string $field, $value)
    {
        return $this->where($field, 'not between', $value);
    }

    /**
     * 返回数据中指定的一列
     *
     * @param string|null $columnKey 键名
     * @param string|null $indexKey  作为索引值的列
     * @return array
     */
    public function column(?string $columnKey, ?string $indexKey = null)
    {
        return array_column($this->items, $columnKey, $indexKey);
    }

    /**
     * 对数组排序
     *
     * @param callable|null $callback 回调
     * @return static<TKey, TValue>
     */
    public function sort(?callable $callback = null)
    {
        $items = $this->items;

        $callback = $callback ?: function ($a, $b) {
            return $a == $b ? 0 : (($a < $b) ? -1 : 1);
        };

        uasort($items, $callback);

        return new static($items);
    }

    /**
     * 指定字段排序
     *
     * @param string $field 排序字段
     * @param string $order 排序
     * @return $this
     */
    public function order(string $field, string $order = 'asc')
    {
        return $this->sort(function ($a, $b) use ($field, $order) {
            $fieldA = $a[$field] ?? null;
            $fieldB = $b[$field] ?? null;

            return 'desc' == strtolower($order) ? ($fieldB <=> $fieldA) : ($fieldA <=> $fieldB);
        });
    }

    /**
     * 将数组打乱
     *
     * @return static<TKey, TValue>
     */
    public function shuffle()
    {
        $items = $this->items;

        shuffle($items);

        return new static($items);
    }

    /**
     * 获取第一个单元数据
     *
     * @param callable|null $callback
     * @param null          $default
     * @return TValue
     */
    public function first(?callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * 获取最后一个单元数据
     *
     * @param callable|null $callback
     * @param null          $default
     * @return TValue
     */
    public function last(?callable $callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }

    /**
     * 截取数组
     *
     * @param int  $offset       起始位置
     * @param int|null $length       截取长度
     * @param bool $preserveKeys preserveKeys
     * @return static<TKey, TValue>
     */
    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    /**
     * @param TKey $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset) : bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @param TKey $offset
     * @return TValue
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @param TKey|null $offset
     * @param TValue $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param TKey $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    //Countable
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    //JsonSerializable
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * 转换当前数据集为JSON字符串
     *
     * @param integer $options json参数
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * 转换成数组
     *
     * @param mixed $items 数据
     * @return array
     */
    protected function convertToArray($items): array
    {
        if ($items instanceof self) {
            return $items->all();
        }

        return (array) $items;
    }
}
