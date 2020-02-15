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
use Closure;
use Countable;
use DomainException;
use IteratorAggregate;
use JsonSerializable;
use think\paginator\driver\Bootstrap;
use Traversable;

/**
 * 分页基础类
 * @mixin Collection
 */
abstract class Paginator implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * 是否简洁模式
     * @var bool
     */
    protected $simple = false;

    /**
     * 数据集
     * @var Collection
     */
    protected $items;

    /**
     * 当前页
     * @var int
     */
    protected $currentPage;

    /**
     * 最后一页
     * @var int
     */
    protected $lastPage;

    /**
     * 数据总数
     * @var integer|null
     */
    protected $total;

    /**
     * 每页数量
     * @var int
     */
    protected $listRows;

    /**
     * 是否有下一页
     * @var bool
     */
    protected $hasMore;

    /**
     * 分页配置
     * @var array
     */
    protected $options = [
        'var_page' => 'page',
        'path'     => '/',
        'query'    => [],
        'fragment' => '',
    ];

    /**
     * 获取当前页码
     * @var Closure
     */
    protected static $currentPageResolver;

    /**
     * 获取当前路径
     * @var Closure
     */
    protected static $currentPathResolver;

    /**
     * @var Closure
     */
    protected static $maker;

    public function __construct($items, int $listRows, int $currentPage = 1, int $total = null, bool $simple = false, array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->options['path'] = '/' != $this->options['path'] ? rtrim($this->options['path'], '/') : $this->options['path'];

        $this->simple   = $simple;
        $this->listRows = $listRows;

        if (!$items instanceof Collection) {
            $items = Collection::make($items);
        }

        if ($simple) {
            $this->currentPage = $this->setCurrentPage($currentPage);
            $this->hasMore     = count($items) > ($this->listRows);
            $items             = $items->slice(0, $this->listRows);
        } else {
            $this->total       = $total;
            $this->lastPage    = (int) ceil($total / $listRows);
            $this->currentPage = $this->setCurrentPage($currentPage);
            $this->hasMore     = $this->currentPage < $this->lastPage;
        }
        $this->items = $items;
    }

    /**
     * @access public
     * @param mixed $items
     * @param int   $listRows
     * @param int   $currentPage
     * @param int   $total
     * @param bool  $simple
     * @param array $options
     * @return Paginator
     */
    public static function make($items, int $listRows, int $currentPage = 1, int $total = null, bool $simple = false, array $options = [])
    {
        if (isset(static::$maker)) {
            return call_user_func(static::$maker, $items, $listRows, $currentPage, $total, $simple, $options);
        }

        return new Bootstrap($items, $listRows, $currentPage, $total, $simple, $options);
    }

    public static function maker(Closure $resolver)
    {
        static::$maker = $resolver;
    }

    protected function setCurrentPage(int $currentPage): int
    {
        if (!$this->simple && $currentPage > $this->lastPage) {
            return $this->lastPage > 0 ? $this->lastPage : 1;
        }

        return $currentPage;
    }

    /**
     * 获取页码对应的链接
     *
     * @access protected
     * @param int $page
     * @return string
     */
    protected function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        if (strpos($this->options['path'], '[PAGE]') === false) {
            $parameters = [$this->options['var_page'] => $page];
            $path       = $this->options['path'];
        } else {
            $parameters = [];
            $path       = str_replace('[PAGE]', $page, $this->options['path']);
        }

        if (count($this->options['query']) > 0) {
            $parameters = array_merge($this->options['query'], $parameters);
        }

        $url = $path;
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters, '', '&');
        }

        return $url . $this->buildFragment();
    }

    /**
     * 自动获取当前页码
     * @access public
     * @param string $varPage
     * @param int    $default
     * @return int
     */
    public static function getCurrentPage(string $varPage = 'page', int $default = 1): int
    {
        if (isset(static::$currentPageResolver)) {
            return call_user_func(static::$currentPageResolver, $varPage);
        }

        return $default;
    }

    /**
     * 设置获取当前页码闭包
     * @param Closure $resolver
     */
    public static function currentPageResolver(Closure $resolver)
    {
        static::$currentPageResolver = $resolver;
    }

    /**
     * 自动获取当前的path
     * @access public
     * @param string $default
     * @return string
     */
    public static function getCurrentPath($default = '/'): string
    {
        if (isset(static::$currentPathResolver)) {
            return call_user_func(static::$currentPathResolver);
        }

        return $default;
    }

    /**
     * 设置获取当前路径闭包
     * @param Closure $resolver
     */
    public static function currentPathResolver(Closure $resolver)
    {
        static::$currentPathResolver = $resolver;
    }

    public function total(): int
    {
        if ($this->simple) {
            throw new DomainException('not support total');
        }

        return $this->total;
    }

    public function listRows(): int
    {
        return $this->listRows;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function lastPage(): int
    {
        if ($this->simple) {
            throw new DomainException('not support last');
        }

        return $this->lastPage;
    }

    /**
     * 数据是否足够分页
     * @access public
     * @return bool
     */
    public function hasPages(): bool
    {
        return !(1 == $this->currentPage && !$this->hasMore);
    }

    /**
     * 创建一组分页链接
     *
     * @access public
     * @param int $start
     * @param int $end
     * @return array
     */
    public function getUrlRange(int $start, int $end): array
    {
        $urls = [];

        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }

        return $urls;
    }

    /**
     * 设置URL锚点
     *
     * @access public
     * @param string|null $fragment
     * @return $this
     */
    public function fragment(string $fragment = null)
    {
        $this->options['fragment'] = $fragment;

        return $this;
    }

    /**
     * 添加URL参数
     *
     * @access public
     * @param array $append
     * @return $this
     */
    public function appends(array $append)
    {
        foreach ($append as $k => $v) {
            if ($k !== $this->options['var_page']) {
                $this->options['query'][$k] = $v;
            }
        }

        return $this;
    }

    /**
     * 构造锚点字符串
     *
     * @access public
     * @return string
     */
    protected function buildFragment(): string
    {
        return $this->options['fragment'] ? '#' . $this->options['fragment'] : '';
    }

    /**
     * 渲染分页html
     * @access public
     * @return mixed
     */
    abstract public function render();

    public function items()
    {
        return $this->items->all();
    }

    /**
     * 获取数据集
     *
     * @return Collection|\think\model\Collection
     */
    public function getCollection()
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * 给每个元素执行个回调
     *
     * @access public
     * @param callable $callback
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
     * Retrieve an external iterator
     * @access public
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * Whether a offset exists
     * @access public
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->items->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @access public
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * Offset to set
     * @access public
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->items->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @access public
     * @param mixed $offset
     * @return void
     * @since  5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->items->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     */
    public function count(): int
    {
        return $this->items->count();
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    public function toArray(): array
    {
        try {
            $total = $this->total();
        } catch (DomainException $e) {
            $total = null;
        }

        return [
            'total'        => $total,
            'per_page'     => $this->listRows(),
            'current_page' => $this->currentPage(),
            'last_page'    => $this->lastPage,
            'data'         => $this->items->toArray(),
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->items, $name], $arguments);

        if ($result instanceof Collection) {
            $this->items = $result;
            return $this;
        }

        return $result;
    }

}
