<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\model\concern;

use InvalidArgumentException;
use think\db\Raw;
use think\helper\Str;
use think\model\Relation;

/**
 * 模型数据处理
 */
trait Attribute
{
    /**
     * 数据表主键 复合主键使用数组定义
     * @var string|array
     */
    protected $pk = 'id';

    /**
     * 数据表字段信息 留空则自动获取
     * @var array
     */
    protected $schema = [];

    /**
     * 当前允许写入的字段
     * @var array
     */
    protected $field = [];

    /**
     * 字段自动类型转换
     * @var array
     */
    protected $type = [];

    /**
     * 数据表废弃字段
     * @var array
     */
    protected $disuse = [];

    /**
     * 数据表只读字段
     * @var array
     */
    protected $readonly = [];

    /**
     * 当前模型数据
     * @var array
     */
    private $data = [];

    /**
     * 原始数据
     * @var array
     */
    private $origin = [];

    /**
     * JSON数据表字段
     * @var array
     */
    protected $json = [];

    /**
     * JSON数据表字段类型
     * @var array
     */
    protected $jsonType = [];

    /**
     * JSON数据取出是否需要转换为数组
     * @var bool
     */
    protected $jsonAssoc = false;

    /**
     * 是否严格字段大小写
     * @var bool
     */
    protected $strict = true;

    /**
     * 修改器执行记录
     * @var array
     */
    private $set = [];

    /**
     * 动态获取器
     * @var array
     */
    private $withAttr = [];

    /**
     * 获取模型对象的主键
     * @access public
     * @return string|array
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * 判断一个字段名是否为主键字段
     * @access public
     * @param  string $key 名称
     * @return bool
     */
    protected function isPk(string $key): bool
    {
        $pk = $this->getPk();

        if (is_string($pk) && $pk == $key) {
            return true;
        } elseif (is_array($pk) && in_array($key, $pk)) {
            return true;
        }

        return false;
    }

    /**
     * 获取模型对象的主键值
     * @access public
     * @return mixed
     */
    public function getKey()
    {
        $pk = $this->getPk();

        if (is_string($pk) && array_key_exists($pk, $this->data)) {
            return $this->data[$pk];
        }

        return;
    }

    /**
     * 设置允许写入的字段
     * @access public
     * @param  array $field 允许写入的字段
     * @return $this
     */
    public function allowField(array $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * 设置只读字段
     * @access public
     * @param  array $field 只读字段
     * @return $this
     */
    public function readOnly(array $field)
    {
        $this->readonly = $field;

        return $this;
    }

    /**
     * 获取实际的字段名
     * @access protected
     * @param  string $name 字段名
     * @return string
     */
    protected function getRealFieldName(string $name): string
    {
        return $this->strict ? $name : Str::snake($name);
    }

    /**
     * 设置数据对象值
     * @access public
     * @param  array    $data  数据
     * @param  bool     $set   是否调用修改器
     * @param  array    $allow 允许的字段名
     * @return $this
     */
    public function data(array $data, bool $set = false, array $allow = [])
    {
        // 清空数据
        $this->data = [];

        // 废弃字段
        foreach ($this->disuse as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }

        if (!empty($allow)) {
            $result = [];
            foreach ($allow as $name) {
                if (isset($data[$name])) {
                    $result[$name] = $data[$name];
                }
            }
            $data = $result;
        }

        if ($set) {
            // 数据对象赋值
            $this->setAttrs($data);
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * 批量追加数据对象值
     * @access public
     * @param  array $data  数据
     * @param  bool  $set   是否需要进行数据处理
     * @return $this
     */
    public function appendData(array $data, bool $set = false)
    {
        if ($set) {
            $this->setAttrs($data);
        } else {
            $this->data = array_merge($this->data, $data);
        }

        return $this;
    }

    /**
     * 获取对象原始数据 如果不存在指定字段返回null
     * @access public
     * @param  string $name 字段名 留空获取全部
     * @return mixed
     */
    public function getOrigin(string $name = null)
    {
        if (is_null($name)) {
            return $this->origin;
        }

        return array_key_exists($name, $this->origin) ? $this->origin[$name] : null;
    }

    /**
     * 获取对象原始数据 如果不存在指定字段返回false
     * @access public
     * @param  string $name 字段名 留空获取全部
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getData(string $name = null)
    {
        if (is_null($name)) {
            return $this->data;
        }

        $fieldName = $this->getRealFieldName($name);

        if (array_key_exists($fieldName, $this->data)) {
            return $this->data[$fieldName];
        } elseif (array_key_exists($fieldName, $this->relation)) {
            return $this->relation[$fieldName];
        }

        throw new InvalidArgumentException('property not exists:' . static::class . '->' . $name);
    }

    /**
     * 获取变化的数据 并排除只读数据
     * @access public
     * @return array
     */
    public function getChangedData(): array
    {
        $data = $this->force ? $this->data : array_udiff_assoc($this->data, $this->origin, function ($a, $b) {
            if ((empty($a) || empty($b)) && $a !== $b) {
                return 1;
            }

            return is_object($a) || $a != $b ? 1 : 0;
        });

        // 只读字段不允许更新
        foreach ($this->readonly as $key => $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * 直接设置数据对象值
     * @access public
     * @param  string $name  属性名
     * @param  mixed  $value 值
     * @return void
     */
    public function set(string $name, $value): void
    {
        $name = $this->getRealFieldName($name);

        $this->data[$name] = $value;
    }

    /**
     * 通过修改器 批量设置数据对象值
     * @access public
     * @param  array $data  数据
     * @return void
     */
    public function setAttrs(array $data): void
    {
        // 进行数据处理
        foreach ($data as $key => $value) {
            $this->setAttr($key, $value, $data);
        }
    }

    /**
     * 通过修改器 设置数据对象值
     * @access public
     * @param  string $name  属性名
     * @param  mixed  $value 属性值
     * @param  array  $data  数据
     * @return void
     */
    public function setAttr(string $name, $value, array $data = []): void
    {
        $name = $this->getRealFieldName($name);

        if (isset($this->set[$name])) {
            return;
        }

        if (is_null($value) && $this->autoWriteTimestamp && in_array($name, [$this->createTime, $this->updateTime])) {
            // 自动写入的时间戳字段
            $value = $this->autoWriteTimestamp();
        } else {
            // 检测修改器
            $method = 'set' . Str::studly($name) . 'Attr';

            if (method_exists($this, $method)) {
                $array = $this->data;

                $value = $this->$method($value, array_merge($this->data, $data));

                $this->set[$name] = true;
                if (is_null($value) && $array !== $this->data) {
                    return;
                }
            } elseif (isset($this->type[$name])) {
                // 类型转换
                $value = $this->writeTransform($value, $this->type[$name]);
            }
        }

        // 设置数据对象属性
        $this->data[$name] = $value;
    }

    /**
     * 数据写入 类型转换
     * @access protected
     * @param  mixed        $value 值
     * @param  string|array $type  要转换的类型
     * @return mixed
     */
    protected function writeTransform($value, $type)
    {
        if (is_null($value)) {
            return;
        }

        if ($value instanceof Raw) {
            return $value;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (strpos($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'float':
                if (empty($param)) {
                    $value = (float) $value;
                } else {
                    $value = (float) number_format($value, (int) $param, '.', '');
                }
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'timestamp':
                if (!is_numeric($value)) {
                    $value = strtotime($value);
                }
                break;
            case 'datetime':
                $value = is_numeric($value) ? $value : strtotime($value);
                $value = $this->formatDateTime('Y-m-d H:i:s.u', $value, true);
                break;
            case 'object':
                if (is_object($value)) {
                    $value = json_encode($value, JSON_FORCE_OBJECT);
                }
                break;
            case 'array':
                $value = (array) $value;
            case 'json':
                $option = !empty($param) ? (int) $param : JSON_UNESCAPED_UNICODE;
                $value  = json_encode($value, $option);
                break;
            case 'serialize':
                $value = serialize($value);
                break;
            default:
                if (is_object($value) && false !== strpos($type, '\\') && method_exists($value, '__toString')) {
                    // 对象类型
                    $value = $value->__toString();
                }
        }

        return $value;
    }

    /**
     * 获取器 获取数据对象的值
     * @access public
     * @param  string $name 名称
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getAttr(string $name)
    {
        try {
            $relation = false;
            $value    = $this->getData($name);
        } catch (InvalidArgumentException $e) {
            $relation = $this->isRelationAttr($name);
            $value    = null;
        }

        return $this->getValue($name, $value, $relation);
    }

    /**
     * 获取经过获取器处理后的数据对象的值
     * @access protected
     * @param  string      $name 字段名称
     * @param  mixed       $value 字段值
     * @param  bool|string $relation 是否为关联属性或者关联名
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function getValue(string $name, $value, $relation = false)
    {
        // 检测属性获取器
        $fieldName = $this->getRealFieldName($name);
        $method    = 'get' . Str::studly($name) . 'Attr';

        if (isset($this->withAttr[$fieldName])) {
            if ($relation) {
                $value = $this->getRelationValue($relation);
            }

            if (in_array($fieldName, $this->json) && is_array($this->withAttr[$fieldName])) {
                $value = $this->getJsonValue($fieldName, $value);
            } else {
                $closure = $this->withAttr[$fieldName];
                $value   = $closure($value, $this->data);
            }
        } elseif (method_exists($this, $method)) {
            if ($relation) {
                $value = $this->getRelationValue($relation);
            }

            $value = $this->$method($value, $this->data);
        } elseif (isset($this->type[$fieldName])) {
            // 类型转换
            $value = $this->readTransform($value, $this->type[$fieldName]);
        } elseif ($this->autoWriteTimestamp && in_array($fieldName, [$this->createTime, $this->updateTime])) {
            $value = $this->getTimestampValue($value);
        } elseif ($relation) {
            $value = $this->getRelationValue($relation);
            // 保存关联对象值
            $this->relation[$name] = $value;
        }

        return $value;
    }

    /**
     * 获取JSON字段属性值
     * @access protected
     * @param  string $name  属性名
     * @param  mixed  $value JSON数据
     * @return mixed
     */
    protected function getJsonValue($name, $value)
    {
        foreach ($this->withAttr[$name] as $key => $closure) {
            if ($this->jsonAssoc) {
                $value[$key] = $closure($value[$key], $value);
            } else {
                $value->$key = $closure($value->$key, $value);
            }
        }

        return $value;
    }

    /**
     * 获取关联属性值
     * @access protected
     * @param  string $relation 关联名
     * @return mixed
     */
    protected function getRelationValue(string $relation)
    {
        $modelRelation = $this->$relation();

        return $modelRelation instanceof Relation ? $this->getRelationData($modelRelation) : null;
    }

    /**
     * 数据读取 类型转换
     * @access protected
     * @param  mixed        $value 值
     * @param  string|array $type  要转换的类型
     * @return mixed
     */
    protected function readTransform($value, $type)
    {
        if (is_null($value)) {
            return;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (strpos($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'float':
                if (empty($param)) {
                    $value = (float) $value;
                } else {
                    $value = (float) number_format($value, (int) $param, '.', '');
                }
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'timestamp':
                if (!is_null($value)) {
                    $format = !empty($param) ? $param : $this->dateFormat;
                    $value  = $this->formatDateTime($format, $value, true);
                }
                break;
            case 'datetime':
                if (!is_null($value)) {
                    $format = !empty($param) ? $param : $this->dateFormat;
                    $value  = $this->formatDateTime($format, $value);
                }
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
            case 'array':
                $value = empty($value) ? [] : json_decode($value, true);
                break;
            case 'object':
                $value = empty($value) ? new \stdClass() : json_decode($value);
                break;
            case 'serialize':
                try {
                    $value = unserialize($value);
                } catch (\Exception $e) {
                    $value = null;
                }
                break;
            default:
                if (false !== strpos($type, '\\')) {
                    // 对象类型
                    $value = new $type($value);
                }
        }

        return $value;
    }

    /**
     * 设置数据字段获取器
     * @access public
     * @param  string|array $name       字段名
     * @param  callable     $callback   闭包获取器
     * @return $this
     */
    public function withAttribute($name, callable $callback = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->withAttribute($key, $val);
            }
        } else {
            $name = $this->getRealFieldName($name);

            if (strpos($name, '.')) {
                [$name, $key] = explode('.', $name);

                $this->withAttr[$name][$key] = $callback;
            } else {
                $this->withAttr[$name] = $callback;
            }
        }

        return $this;
    }

}
