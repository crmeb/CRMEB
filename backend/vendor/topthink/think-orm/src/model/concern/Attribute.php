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
declare (strict_types = 1);

namespace think\model\concern;

use BackedEnum;
use Stringable;
use think\db\Express;
use think\db\Raw;
use think\helper\Str;
use think\model\Collection;
use think\model\contract\EnumTransform;
use think\model\contract\FieldTypeTransform;
use think\model\contract\Modelable as Model;
use think\model\contract\Typeable;
use think\model\type\Date;
use think\model\type\DateTime;
use think\model\type\Json;

/**
 * 模型数据处理.
 */
trait Attribute
{
    /**
     * 初始化模型数据.
     *
     * @param array|object $data 实体模型数据
     * @param bool  $fromSave
     *
     * @return void
     */
    private function initializeData(array | object $data, bool $fromSave = false)
    {
        // 分析数据
        $data    = $this->parseData($data);
        $schema  = $this->getFields();
        $fields  = array_keys($schema);

        // 模型赋值
        foreach ($data as $name => $value) {
            if (in_array($name, $this->getOption('disuse'))) {
                // 废弃字段
                continue;
            }

            if (str_contains($name, '__')) {
                // 组装关联JOIN查询数据
                [$relation, $attr] = explode('__', $name, 2);

                $relations[$relation][$attr] = $value;
                continue;
            }

            $trueName = $fromSave ? $this->getMappingName($name) : $name;
            if (in_array($trueName, $fields)) {
                $type = $schema[$trueName] ?? 'string';
                // 读取数据后进行类型转换
                if (!$fromSave || !$this->hasSetAttr($trueName)) {
                    $value = $this->readTransform($value, $type);
                }
                // 数据赋值
                $this->setData($trueName, $value);
                if ($trueName == $this->getPk()) {
                    $this->setKey($value);
                }
                // 记录原始数据
                $origin[$trueName] = $value;
            } else {
                // 非数据表字段或关联数据 额外赋值
                $this->setData($trueName, $value);
            }
        }

        if (!empty($relations)) {
            // 设置关联数据
            $this->parseRelationData($relations);
        }

        if (!empty($origin) && !$fromSave) {
            $this->trigger('AfterRead');
            $this->setOption('origin', $origin);
            $this->setOption('get', []);
        }
    }

    /**
     * 获取主键名.
     *
     * @return null|string|array
     */
    public function getPk()
    {
        return $this->getOption('pk');
    }

    /**
     * 获取表名（不含前后缀）.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getOption('name', Str::snake(class_basename(static::class)));
    }

    /**
     * 解析模型数据.
     *
     * @param array|object $data 数据
     *
     * @return array
     */
    private function parseData(array | object $data): array
    {
        if ($data instanceof self) {
            $data = $data->getData();
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }

        return $data;
    }

    /**
     * 动态设置数据字段获取器.
     *
     * @param array|string $attr     字段名
     * @param callable     $callback 闭包获取器
     *
     * @return $this
     */
    public function withFieldAttr(array | string $attr, ?callable $callback = null)
    {
        if (is_array($attr)) {
            foreach ($attr as $name => $closure) {
                $this->withFieldAttr($name, $closure);
            }
        } else {
            $name = $this->getRealFieldName($attr);
            $this->setWeakData('withAttr', $name, $callback);
            // 自动追加输出
            self::$weakMap[$this]['append'][] = $name;
        }

        return $this;
    }

    /**
     * 获取实际字段名.
     * 严格模式下 完全和数据表字段对应一致（默认）
     * 非严格模式 统一转换为snake规范（支持驼峰规范读取）
     *
     * @param string $name  字段名
     *
     * @return mixed
     */
    protected function getRealFieldName(string $name)
    {
        if (false === $this->getOption('strict')) {
            return Str::snake($name);
        }

        return $name;
    }

    /**
     * 数据读取 类型转换.
     *
     * @param mixed             $value 值
     * @param string|array|null $type  要转换的类型
     *
     * @return mixed
     */
    protected function readTransform($value, string | array | null $type)
    {
        if (is_null($type) || is_null($value) || $value instanceof Raw || $value instanceof Express) {
            return $value;
        }

        $param = '';
        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (str_contains($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        $typeTransform = static function (string $type, $value, $model, $param) {
            if (class_exists($type) && !($value instanceof $type)) {
                if (is_subclass_of($type, Typeable::class)) {
                    $value = $type::from($value, $model);
                    if ($param && $value instanceof DateTime) {
                        // 设置时间输出格式
                        $value->setFormat($param);
                    }
                } elseif (is_subclass_of($type, FieldTypeTransform::class)) {
                    $value = $type::get($value, $model);
                } elseif (is_subclass_of($type, BackedEnum::class)) {
                    $value = $type::from($value);
                    if (is_subclass_of($type, EnumTransform::class)) {
                        $value = $value->value();
                    } elseif ($model->getOption('enumReadName')) {
                        $method = $model->getOption('enumReadName');
                        $value  = is_string($method) ? $value->$method() : $value->name;
                    }
                } else {
                    // 对象类型
                    $value = new $type($value);
                }
            }
            return $value;
        };

        return match ($type) {
            'string','bigint'=> (string) $value,
            'int','integer'  => (int) $value,
            'float'          => empty($param) ? (float) $value : (float) number_format($value, (int) $param, '.', ''),
            'bool','boolean' => (bool) $value,
            'array'          => empty($value) ? [] : (is_array($value) ? $value : json_decode($value, true)),
            'object'         => empty($value) ? new \stdClass() : (is_string($value) ? json_decode($value) : json_decode(json_encode($value, JSON_FORCE_OBJECT))),
            'json'           => $typeTransform(Json::class, $value, $this, $param),
            'date'           => $typeTransform(Date::class, $value, $this, $param),
            'datetime'       => $typeTransform(DateTime::class, $value, $this, $param),
            'timestamp'      => $typeTransform(DateTime::class, $value, $this, $param),
            default          => $typeTransform($type, $value, $this, $param),
        };
    }

    /**
     * 数据写入 类型转换.
     *
     * @param mixed             $value 值
     * @param string|array|null $type  要转换的类型
     *
     * @return mixed
     */
    protected function writeTransform($value, string | array | null $type)
    {
        if (is_null($type) || is_null($value) || $value instanceof Raw || $value instanceof Express) {
            return $value;
        }

        $param = '';
        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (str_contains($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        $typeTransform = static function (string $type, $value, $model) {
            if (class_exists($type)) {
                if (is_subclass_of($type, Typeable::class)) {
                    $value = $value->value();
                } elseif (is_subclass_of($type, FieldTypeTransform::class)) {
                    $value = $type::set($value, $model);
                } elseif ($value instanceof BackedEnum) {
                    $value = $value->value;
                } elseif ($value instanceof Stringable) {
                    $value = $value->__toString();
                }
            }
            return $value;
        };

        return match ($type) {
            'string','bigint' => (string) $value,
            'int', 'integer'  => (int) $value,
            'float'          => empty($param) ? (float) $value : (float) number_format($value, (int) $param, '.', ''),
            'bool', 'boolean' => $value ? 1 : 0,
            'object'         => is_object($value) ? json_encode($value, JSON_FORCE_OBJECT) : $value,
            'array'          => json_encode((array) $value, JSON_UNESCAPED_UNICODE),
            'json'           => $typeTransform(Json::class, $value, $this),
            'date'           => $typeTransform(Date::class, $value, $this),
            'datetime'       => $typeTransform(DateTime::class, $value, $this),
            'timestamp'      => $typeTransform(DateTime::class, $value, $this),
            default          => $typeTransform($type, $value, $this),
        };
    }

    /**
     * 刷新对象原始数据（为当前数据）.
     *
     * @return $this
     */
    public function refreshOrigin()
    {
        return $this->setOption('origin', $this->getData());
    }

    /**
     * 设置主键值
     *
     * @param int|string $value 值
     * @return void
     */
    public function setKey($value)
    {
        $pk = $this->getPk();

        if (is_string($pk)) {
            $this->set($pk, $value);
        }
    }

    /**
     * 获取主键值
     *
     * @return mixed
     */
    public function getKey()
    {
        $pk = $this->getPk();
        if (is_null($pk)) {
            return;
        }
        
        if (is_string($pk)) {
            return $this->get($pk);
        }

        foreach ($pk as $name) {
            $data[$name] = $this->get($name);
        }
        return $data;
    }

    /**
     * 重置模型数据.
     *
     * @param array $data
     *
     * @return $this
     */
    public function data(array $data)
    {
        $this->initializeData($data);
        return $this;
    }

    /**
     * 获取模型实际数据.
     *
     * @param string|null $name 字段名
     * @return mixed
     */
    public function getData(?string $name = null)
    {
        if ($name) {
            $name = $this->getRealFieldName($name);
            return $this->getWeakData('data', $name);
        }
        return $this->getOption('data', []);
    }

    /**
     * 判断模型是否存在数据字段.
     *
     * @param string $name 字段名
     * @return bool
     */
    public function hasData(string $name): bool
    {
        return $this->hasGetAttr($name) || array_key_exists($this->getMappingName($name), self::$weakMap[$this]['data']);
    }

    /**
     * 设置数据对象的实际值
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void
     */
    protected function setData(string $name, $value)
    {
        $this->setWeakData('data', $name, $value);
        if ($this->getWeakData('get', $name)) {
            $this->setWeakData('get', $name, null);
        }
    }

    /**
     * 清空模型数据.
     *
     * @return $this
     */
    public function clear()
    {
        $this->setOption('data', []);
        $this->setOption('origin', []);
        $this->setOption('get', []);
        $this->setOption('relation', []);
        return $this;
    }

    /**
     * 获取原始数据.
     *
     * @param string|null $name 字段名
     * @param bool $transform 是否自动类型转换
     * @return mixed
     */
    public function getOrigin(?string $name = null, bool $transfrom = false)
    {
        if ($name) {
            $name   = $this->getRealFieldName($name);
            $result = $this->getWeakData('origin', $name);
            return $transfrom ? $this->writeTransform($result, $this->getFields($name)) : $result;
        }
        return $this->getOption('origin');
    }

    /**
     * 判断数据是否为空.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getData());
    }

    /**
     * 判断JSON数据是否为数组格式.
     *
     * @return bool|null
     */
    public function isJsonAssoc(): bool|null
    {
        return $this->getOption('jsonAssoc', true);
    }

    /**
     * 设置JSON数据格式.
     *
     * @return $this
     */
    public function jsonAssoc(bool $assoc = true)
    {
        return $this->setOption('jsonAssoc', $assoc);
    }

    /**
     * 设置数据对象的值 并进行类型自动转换
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return $this
     */
    public function set(string $name, $value)
    {
        $name = $this->getMappingName($name);
        $type = $this->getFields()[$name] ?? '';

        if ($this->isExists() && in_array($name, $this->getOption('readonly'))) {
            // 只读属性不能赋值
            return $this;
        }

        if (is_null($value) && is_subclass_of($type, Model::class)) {
            // 关联数据为空 设置一个空模型
            $value = new $type();
        } elseif (!($value instanceof Model || $value instanceof Collection || $value instanceof FieldTypeTransform) && $type && !$this->hasSetAttr($name)) {
            // 类型自动转换
            $value = $this->readTransform($value, $type);
        }

        $this->setData($name, $value);
        return $this;
    }

    /**
     * 字段是否定义修改器
     *
     * @param string $name  名称
     *
     * @return bool
     */
    protected function hasSetAttr(string $name): bool
    {
        $attr   = Str::studly($name);
        $method = 'set' . $attr . 'Attr';
        return method_exists($this, $method);
    }

    /**
     * 字段是否定义获取器
     *
     * @param string $name  名称
     *
     * @return bool
     */
    protected function hasGetAttr(string $name): bool
    {
        $attr   = Str::studly($name);
        $method = 'get' . $attr . 'Attr';
        return method_exists($this, $method);
    }

    /**
     * 使用修改器或类型自动转换处理数据（写入数据前自动调用）
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return mixed
     */
    private function setAttrOfWith(string $name, $value)
    {
        $attr   = Str::studly($name);
        $method = 'set' . $attr . 'Attr';
        if (method_exists($this, $method)) {
            $value = $this->$method($value, $this->getData());
        } else {
            // 类型转换
            $value = $this->writeTransform($value, $this->getFields($name));
        }

        if ($value instanceof Express) {
            // 处理运算表达式
            $step   = $value->getStep();
            $origin = $this->getOrigin($name);
            $real   = match ($value->getType()) {
                '+'              => $origin + $step,
                '-'              => $origin - $step,
                '*'              => $origin * $step,
                '/'              => $origin / $step,
                default          => $origin,
            };
            $this->set($name, $real);
        } elseif (is_scalar($value)) {
            // 同步写入修改器或类型自动转换结果
            $this->set($name, $value);
        }

        return $value;
    }

    /**
     * 获取数据对象的值（支持使用获取器）
     *
     * @param string $name 名称
     * @param bool   $attr 是否使用获取器
     *
     * @return mixed
     */
    public function get(string $name, bool $attr = true)
    {
        $name = $this->getMappingName($name);
        if ($attr && $value = $this->getWeakData('get', $name)) {
            // 已经输出的数据直接返回
            return $value;
        }

        if (!array_key_exists($name, $this->getData()) && !array_key_exists($name, $this->getFields())) {
            // 动态获取关联数据
            $value = $this->getRelationData($name) ?: null;
        } else {
            $value = $this->getData($name);
        }

        if ($attr) {
            // 通过获取器输出
            $value = $this->getAttrOfWith($name, $value, $this->getData());
            $this->setWeakData('get', $name, $value);
        }

        return $value;
    }

    /**
     * 获取映射字段
     *
     * @param string $name 名称
     *
     * @return string
     */
    protected function getMappingName(string $name): string
    {
        $mapping = $this->getOption('mapping');
        return array_search($name, $mapping) ?: $this->getRealFieldName($name);
    }

    /**
     * 处理数据对象的值（经过获取器和类型转换）
     *
     * @param string $name 名称
     * @param mixed  $value 值
     * @param array  $data 所有数据
     *
     * @return mixed
     */
    private function getAttrOfWith(string $name, $value, array $data = [])
    {
        $attr     = Str::studly($name);
        $method   = 'get' . $attr . 'Attr';
        $withAttr = $this->getWeakData('withAttr', $name);
        if ($withAttr) {
            // 动态获取器
            $value = $withAttr($value, $data, $this);
        } elseif (method_exists($this, $method)) {
            // 获取器
            $value = $this->$method($value, $data);
        } elseif ($value instanceof Typeable || is_subclass_of($value, EnumTransform::class, false)) {
            // 类型自动转换
            if ($value instanceof Json) {
                // JSON数据转换
                $value = $this->readTransformJson($name, $value);
            } else {
                $value = $value->value();
            }
        } elseif (is_int($value) && $this->isTimeAttr($name) && false != $this->getDateFormat()) {
            // 兼容数字类型时间字段的自动转换输出
            $value = (new \DateTime())
                ->setTimestamp($value)
                ->format($this->getDateFormat());
        }
        return $value;
    }

    /**
     * 处理JSON数据对象的值
     *
     * @param string $name 名称
     * @param Json   $value 值
     *
     * @return array|object
     */
    protected function readTransformJson(string $name, Json $value)
    {
        // JSON数据转换
        $value = $value->value();
        if ($value) {
            foreach ($value as $key => &$val) {
                $type = $this->getFields($name . '->' . $key);
                if ($type) {
                    // 定义了JSON属性类型自动转换
                    $val  = $this->readTransform($val, $type);
                }
            }            
        }
        return $value;
    }

    protected function isTimeAttr(string $name): bool
    {
        return in_array($name, [$this->getOption('createTime'), $this->getOption('updateTime'), $this->getOption('deleteTime')]) || in_array($name, $this->getOption('timestampField', []));
    }

    /**
     * 使用获取器获取数据对象的值
     *
     * @param string $name 名称
     *
     * @return mixed
     */
    public function getAttr(string $name)
    {
        return $this->get($name);
    }

    /**
     * 设置数据对象的值 并进行类型自动转换
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return $this
     */
    public function setAttr(string $name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * 批量设置数据对象值 支持数据类型转换
     *
     * @param array $data 数据
     *
     * @return void
     */
    public function setAttrs(array $data): void
    {
        // 进行数据处理
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * 设置数据是否存在.
     *
     * @param bool $exists
     *
     * @return $this
     */
    public function exists(bool $exists = true)
    {
        return $this->setOption('exists', $exists);
    }

    /**
     * 判断数据是否存在数据库.
     *
     * @return bool
     */
    public function isExists(): bool
    {
        return $this->getOption('exists', false);
    }

    /**
     * 设置枚举类型自动读取数据方式
     * true 表示使用name值返回
     * 字符串 表示使用枚举类的方法返回
     *
     * @return $this
     */
    public function withEnumRead(bool | string $method = true)
    {
        return $this->setOption('enumReadName', $method);
    }
}
