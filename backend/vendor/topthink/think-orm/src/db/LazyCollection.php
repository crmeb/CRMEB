<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\db;

use Countable;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use think\Collection;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\Model;
use Traversable;

/**
 * 惰性集合类 - 用于高效处理大数据集.
 *
 * @template TKey of array-key
 * @template TModel of \think\Model
 *
 * @implements IteratorAggregate<TKey, TModel>
 */
class LazyCollection implements IteratorAggregate, Countable, JsonSerializable, Arrayable, Jsonable
{
    /**
     * 数据源生成器
     * @var \Closure|Generator
     */
    protected $source;

    /**
     * 缓存的总数
     * @var int|null
     */
    protected ?int $cachedCount = null;

    /**
     * 构造函数
     * @param \Closure|Generator|BaseQuery $source 数据源
     */
    public function __construct($source)
    {
        if ($source instanceof BaseQuery) {
            $this->source = function () use ($source) {
                yield from $source->cursor();
            };
        } elseif ($source instanceof \Closure) {
            $this->source = $source;
        } elseif ($source instanceof Generator) {
            $this->source = function () use ($source) {
                yield from $source;
            };
        } else {
            throw new \InvalidArgumentException('Invalid source type for LazyCollection');
        }
    }

    /**
     * 创建惰性集合实例
     * @param mixed $items 数据源
     * @return static
     */
    public static function make($items = [])
    {
        if ($items instanceof static) {
            return $items;
        }

        if (is_array($items)) {
            return new static(function () use ($items) {
                foreach ($items as $key => $value) {
                    yield $key => $value;
                }
            });
        }

        return new static($items);
    }

    /**
     * 获取生成器
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return ($this->source)();
    }

    /**
     * 映射集合项到新的惰性集合
     * @param callable $callback 回调函数
     * @return static
     */
    public function map(callable $callback)
    {
        return new static(function () use ($callback) {
            foreach ($this->getIterator() as $key => $value) {
                yield $key => $callback($value, $key);
            }
        });
    }

    /**
     * 过滤集合项
     * @param callable|null $callback 回调函数
     * @return static
     */
    public function filter(?callable $callback = null)
    {
        if (is_null($callback)) {
            $callback = function ($value) {
                return (bool) $value;
            };
        }

        return new static(function () use ($callback) {
            foreach ($this->getIterator() as $key => $value) {
                if ($callback($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * 遍历集合项
     * @param callable $callback 回调函数
     * @return void
     */
    public function each(callable $callback): void
    {
        foreach ($this->getIterator() as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }
    }

    /**
     * 获取前N个元素
     * @param int $limit 数量限制
     * @return static
     */
    public function take(int $limit)
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('Limit should be non-negative');
        }

        return new static(function () use ($limit) {
            $count = 0;
            foreach ($this->getIterator() as $key => $value) {
                if ($count >= $limit) {
                    break;
                }
                yield $key => $value;
                $count++;
            }
        });
    }

    /**
     * 跳过前N个元素
     * @param int $offset 跳过数量
     * @return static
     */
    public function skip(int $offset)
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('Offset should be non-negative');
        }

        return new static(function () use ($offset) {
            $count = 0;
            foreach ($this->getIterator() as $key => $value) {
                if ($count >= $offset) {
                    yield $key => $value;
                }
                $count++;
            }
        });
    }

    /**
     * 分页获取数据
     * @param int $page 页码（从1开始）
     * @param int $listRows 每页数量
     * @return static
     */
    public function page(int $page, int $listRows = 15)
    {
        if ($page < 1 || $listRows < 1) {
            throw new \InvalidArgumentException(
                $page < 1 ? 'Page should be at least 1' : 'Per page should be at least 1'
            );
        }

        $offset = ($page - 1) * $listRows;
        return new static(function () use ($offset, $listRows) {
            $index = 0;
            $taken = 0;
            foreach ($this->getIterator() as $key => $value) {
                if ($index++ < $offset) {
                    continue;
                }

                yield $key => $value;
                
                if (++$taken >= $listRows) {
                    break;
                }
            }
        });
    }

    /**
     * 扁平化集合
     * @param int|float $depth 深度
     * @return static
     */
    public function flatten($depth = INF)
    {
        return new static(function () use ($depth) {
            foreach ($this->getIterator() as $item) {
                if (is_array($item) || $item instanceof Traversable) {
                    if ($depth === 1) {
                        foreach ($item as $value) {
                            yield $value;
                        }
                    } else {
                        foreach (static::make($item)->flatten($depth - 1) as $value) {
                            yield $value;
                        }
                    }
                } else {
                    yield $item;
                }
            }
        });
    }

    /**
     * 转换为数组
     * @return array
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->getIterator() as $key => $value) {
            if ($value instanceof Arrayable) {
                $items[$key] = $value->toArray();
            } else {
                $items[$key] = $value;
            }
        }
        return $items;
    }

    /**
     * 转换为JSON
     * @param int $options JSON选项
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * JsonSerializable接口实现
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 获取集合数量
     * @return int
     */
    public function count(): int
    {
        if ($this->cachedCount !== null) {
            return $this->cachedCount;
        }

        $count = 0;
        foreach ($this->getIterator() as $value) {
            $count++;
        }

        $this->cachedCount = $count;
        return $count;
    }

    /**
     * 判断是否为空
     * @return bool
     */
    public function isEmpty(): bool
    {
        foreach ($this->getIterator() as $value) {
            return false;
        }
        return true;
    }

    /**
     * 获取第一个元素
     * @param callable|null $callback 回调函数
     * @param mixed $default 默认值
     * @return mixed
     */
    public function first(?callable $callback = null, $default = null)
    {
        foreach ($this->getIterator() as $key => $value) {
            if (is_null($callback) || $callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * 获取最后一个元素
     * @param callable|null $callback 回调函数
     * @param mixed $default 默认值
     * @return mixed
     */
    public function last(?callable $callback = null, $default = null)
    {
        $last = $default;
        $lastKey = null;

        foreach ($this->getIterator() as $key => $value) {
            if (is_null($callback) || $callback($value, $key)) {
                $last = $value;
                $lastKey = $key;
            }
        }

        return $last;
    }

    /**
     * 按键值对集合进行分组
     * @param callable|string|int $groupBy 分组依据
     * @return static
     */
    public function group($groupBy)
    {
        return new static(function () use ($groupBy) {
            $groups = [];
            foreach ($this->getIterator() as $key => $value) {
                if (is_callable($groupBy)) {
                    $groupKey = $groupBy($value, $key);
                } else {
                    $groupKey = data_get($value, $groupBy);
                }

                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [];
                }
                $groups[$groupKey][$key] = $value;
            }
            foreach ($groups as $key => $group) {
                yield $key => new Collection($group);
            }
        });
    }


    /**
     * 对集合进行排序
     * @param callable|null $callback 排序回调
     * @return Collection
     */
    public function sort(?callable $callback = null): Collection
    {
        $items = $this->toArray();

        if (is_null($callback)) {
            asort($items);
        } else {
            uasort($items, $callback);
        }
        return new Collection($items);
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
     * 对集合进行反转
     * @return static
     */
    public function reverse()
    {
        return new static(function () {
            $items = [];
            foreach ($this->getIterator() as $key => $value) {
                $items[] = [$key, $value];
            }

            for ($i = count($items) - 1; $i >= 0; $i--) {
                yield $items[$i][0] => $items[$i][1];
            }
        });
    }



    /**
     * 使用回调函数迭代集合项并将结果传递给下一次迭代
     * @param callable $callback 回调函数
     * @param mixed $initial 初始值
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        $result = $initial;

        foreach ($this->getIterator() as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * 执行给定的回调，除非给定的条件为真
     * @param bool $condition 条件
     * @param callable $callback 回调函数
     * @param callable|null $default 默认回调
     * @return static
     */
    public function when(bool $condition, callable $callback, ?callable $default = null)
    {
        if ($condition) {
            return $callback($this) ?: $this;
        } elseif ($default) {
            return $default($this) ?: $this;
        }

        return $this;
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

        return $this->filter(function ($item) use ($field, $operator, $value) {
            $result = data_get($item, $field);
            
            return match ($operator) {
                '!='        => $result != $value,
                '<>'        => $result != $value,
                '<'         => $result < $value,
                '<='        => $result <= $value,
                '>'         => $result > $value,
                '>='        => $result >= $value,
                '==='       => $result === $value,
                '!=='       => $result !== $value,
                'in'        => is_scalar($result) && is_array($value) && in_array($result, $value),
                'not in'    => is_scalar($result) && is_array($value) && !in_array($result, $value),
                'between'   => is_array($value) && count($value) === 2 && ($result >= $value[0] && $result <= $value[1]),
                'not between' => is_array($value) && count($value) === 2 && ($result < $value[0] || $result > $value[1]),
                'like'      => is_string($result) && str_contains($result, $value),
                'not like'  => is_string($result) && !str_contains($result, $value),
                'ilike'     => is_string($result) && str_contains(strtolower($result), strtolower($value)),
                'start'=> is_string($result) && str_starts_with($result, $value),
                'end'  => is_string($result) && str_ends_with($result, $value),
                default     => $result == $value,
            };
        });
    }

    /**
     * LIKE过滤
     *
     * @param string $field 字段名
     * @param string $value 数据
     * @param bool   $case  区分大小写
     * @return static<TKey, TValue>
     */
    public function whereLike(string $field, string $value, bool $case = true)
    {
        return $this->where($field, $case ? 'like' : 'ilike', $value);
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
     * 分块处理数据
     * @param int $size 块大小
     * @return static
     */
    public function chunk(int $size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Size should be greater than 0');
        }

        return new static(function () use ($size) {
            $chunk = [];
            $count = 0;

            foreach ($this->getIterator() as $key => $value) {
                $chunk[$key] = $value;
                $count++;

                if ($count >= $size) {
                    yield new Collection($chunk);
                    $chunk = [];
                    $count = 0;
                }
            }

            if (!empty($chunk)) {
                yield new Collection($chunk);
            }
        });
    }
}