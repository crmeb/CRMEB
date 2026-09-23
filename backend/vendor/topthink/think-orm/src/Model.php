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

namespace think;

use ArrayAccess;
use Closure;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\db\BaseQuery as Query;
use think\db\Express;
use think\exception\ValidateException;
use think\model\Collection;
use think\model\contract\Modelable;
use think\model\View;
use WeakMap;

/**
 * Class Model.
 * @mixin \think\db\Query
 *
 * @method static void  onAfterRead(Model $model)     after_read事件定义
 * @method static mixed onBeforeInsert(Model $model)  before_insert事件定义
 * @method static void  onAfterInsert(Model $model)   after_insert事件定义
 * @method static mixed onBeforeUpdate(Model $model)  before_update事件定义
 * @method static void  onAfterUpdate(Model $model)   after_update事件定义
 * @method static mixed onBeforeWrite(Model $model)   before_write事件定义
 * @method static void  onAfterWrite(Model $model)    after_write事件定义
 * @method static mixed onBeforeDelete(Model $model)  before_write事件定义
 * @method static void  onAfterDelete(Model $model)   after_delete事件定义
 * @method static void  onBeforeRestore(Model $model) before_restore事件定义
 * @method static void  onAfterRestore(Model $model)  after_restore事件定义
 */
abstract class Model implements JsonSerializable, ArrayAccess, Arrayable, Jsonable, Modelable
{
    use model\concern\Attribute;
    use model\concern\AutoWriteData;
    use model\concern\Conversion;
    use model\concern\DbConnect;
    use model\concern\ModelEvent;
    use model\concern\RelationShip;

    private static ?WeakMap $weakMap = null;

    /**
     * 服务注入.
     *
     * @var Closure[]
     */
    protected static array $_maker = [];

    /**
     * 设置服务注入.
     */
    public static function maker(Closure $maker): void
    {
        static::$_maker[] = $maker;
    }

    /**
     * 设置容器对象的依赖注入方法.（用于兼容）
     *
     * @param callable $callable 依赖注入方法
     *
     * @return void
     */
    public static function setInvoker(callable $callable): void
    {
    }

    /**
     * 调用反射执行模型方法 支持参数绑定.
     *
     * @param mixed $method
     * @param array $vars   参数
     *
     * @return mixed
     */
    public function invoke($method, array $vars = [])
    {
        if (is_string($method)) {
            $method = [$this, $method];
        }

        $invoker = $this->getOption('invoker');
        if ($invoker) {
            return $invoker($method instanceof Closure ? $method : Closure::fromCallable($method), $vars);
        }

        return call_user_func_array($method, $vars);
    }

    /**
     * 架构函数.
     *
     * @param array|object $data 实体模型数据
     */
    public function __construct(array | object $data = [])
    {
        // 获取实体模型参数
        $options = array_merge($this->getBaseOptions(), $this->getOptions());

        if (!self::$weakMap) {
            self::$weakMap = new WeakMap;
        }

        self::$weakMap[$this] = [
            'get'          => [],
            'data'         => [],
            'origin'       => [],
            'relation'     => [],
            'together'     => [],
            'allow'        => [],
            'withAttr'     => [],
            'schema'       => $options['schema'] ?? [],
            'updateTime'   => $options['updateTime'] ?? 'update_time',
            'createTime'   => $options['createTime'] ?? 'create_time',
            'suffix'       => $options['suffix'] ?? '',
            'validate'     => $options['validate'] ?? $this->parseValidate(),
            'type'         => $options['type'] ?? [],
            'readonly'     => $options['readonly'] ?? [],
            'disuse'       => $options['disuse'] ?? [],
            'hidden'       => $options['hidden'] ?? [],
            'visible'      => $options['visible'] ?? [],
            'append'       => $options['append'] ?? [],
            'mapping'      => $options['mapping'] ?? [],
            'strict'       => $options['strict'] ?? true,
            'bindAttr'     => $options['bindAttr'] ?? [],
            'autoRelation' => $options['autoRelation'] ?? [],
        ];

        // 设置额外参数
        $this->setOptions(array_diff_key($options, self::$weakMap[$this]));

        // 模型初始化
        $this->initialize();

        // 初始化数据
        $this->initializeData($data);        
    }

    protected function initialize()
    {
        if (!empty(static::$_maker)) {
            foreach (static::$_maker as $maker) {
                call_user_func($maker, $this);
            }
        }

        // 初始化模型
        $this->init();
    }

    /**
     *  初始化模型.
     *
     * @return void
     */
    protected function init()
    {}

    /**
     * 定义基础配置参数.
     *
     * @return array
     */
    protected function getBaseOptions(): array
    {
        return [];
    }

    /**
     * 在实体模型中定义 返回相关配置参数.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * 批量设置模型参数
     * @param array  $options  值
     * @return void
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * 设置模型参数
     *
     * @param string $name  参数名
     * @param mixed  $value  值
     *
     * @return $this
     */
    public function setOption(string $name, $value)
    {
        self::$weakMap[$this][$name] = $value;
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * 获取模型参数
     *
     * @param string $name  参数名
     * @param mixed  $default  默认值
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        // 兼容读取3.0版本的属性参数定义
        if (property_exists($this, $name) && isset($this->$name)) {
            return $this->$name;
        } 
        return self::$weakMap[$this][$name] ?? $default;
    }

    private function setWeakData(string $key, string $name, $value): void
    {
        self::$weakMap[$this][$key][$name] = $value;
    }

    private function getWeakData(string $key, string $name, $default = null)
    {
        return self::$weakMap[$this][$key][$name] ?? $default;
    }

    /**
     * 创建新的模型实例.
     *
     * @param array|object $data
     * @param array $options
     *
     * @return Model|Entity
     */
    public function newInstance(array | object $data = [], array $options = [])
    {
        $model = new static($data);
        if (!empty($data)) {
            $model->exists(true);
        }

        if ($this->getEntity()) {
            // 存在对应实体模型实例
            return $this->getEntity()->newInstance($model, $options);
        }

        return $this->fetchModel($model, $options);
    }

    /**
     * 获取克隆的模型实例.
     *
     * @return static
     */
    public function clone()
    {
        $model = new static();
        self::$weakMap[$model] = self::$weakMap[$this];
        return $model;
    }

    /**
     * 获取实际模型实例.
     *
     * @param Model $model
     *
     * @return Modelable
     */
    protected function fetchModel(Model $model, array $options = []): Modelable
    {
        $class = $model->getOption('entityClass', str_replace('\\model\\', '\\entity\\', static::class));
        if (class_exists($class) && is_subclass_of($class, Entity::class)) {
            return new $class($model, $options);
        }
        return $model;
    }

    public function entity(Entity $entity): void
    {
        $this->setOption('entity', $entity);
    }

    public function getEntity(): ?Entity
    {
        return $this->getOption('entity');
    }

    /**
     * 解析对应验证类.
     *
     * @return string
     */
    protected function parseValidate(): string
    {
        $auto     = $this->getOption('autoValidate', false);
        $validate = $auto && str_contains(static::class, '\\model\\') ? str_replace('\\model\\', '\\validate\\', static::class) : '';
        return $validate && class_exists($validate) ? $validate : '';
    }

    /**
     * 设置验证场景. 
     *
     * @param string|array $scene 场景名或数组
     * @return $this
     */
    public function scene(string|array $scene)
    {
        return $this->setOption('scene', $scene);
    }

    /**
     * 验证模型数据.
     *
     * @param array $data 数据
     * @param array $allow 需要验证的字段
     *
     * @throws ValidateException
     * @return void
     */
    protected function validate(array $data, array $allow = []): void
    {
        $validater = $this->getOption('validate');
        if (!empty($validater)) {
            validate($validater)
                ->scene($this->getOption('scene') ?: $allow)
                ->check($data);
        }
    }

    /**
     * 保存模型实例数据.
     *
     * @param array|object $data 数据
     * @param mixed $where 更新条件 true为强制新增
     * @param bool  $refresh  是否刷新数据
     * @return bool
     */
    public function save(array | object $data = [], $where = [], bool $refresh = false): bool
    {
        if (!empty($data)) {
            // 初始化模型数据
            $this->initializeData($data, true);
        }

        if (false === $this->trigger('BeforeWrite')) {
            return false;
        }

        if (true === $where) {
            $isUpdate = false;
            $where    = [];
        } elseif (!empty($where)) {
            $isUpdate = true;
        } else {
            $isUpdate = $this->isExists() ? true : false;
        }

        if (false === $this->trigger($isUpdate ? 'BeforeUpdate' : 'BeforeInsert')) {
            return false;
        }

        [$data, $relations, $allow] = $this->validateAndFilterData($isUpdate);

        if (empty($data)) {
            // 保存关联数据
            if ($isUpdate && $this->getOption('together')) {
                $this->relationSave($relations, $isUpdate);
            }
            return true;
        }

        // 自动写入数据
        $this->autoWriteData($data, $isUpdate, $allow);

        $db     = $this->getDbWhere($where);
        $result = $db->field($allow)
            ->removeOption('data')
            ->save($data, !$isUpdate);

        if (!$isUpdate) {
            $this->exists(true);
            // 写入自增键值
            $key = $db->getAutoInc();
            $val = $db->getLastInsID();
            if ($key && $val) {
                $this->setData($key, $val);
            }
        } elseif ($refresh) {
            // 刷新数据
            $this->refresh();
        }
        $this->trigger($isUpdate ? 'AfterUpdate' : 'AfterInsert');
        $this->trigger('AfterWrite');

        // 保存关联数据
        if ($this->getOption('together')) {
            $this->relationSave($relations, $isUpdate);
        }

        // 重置原始数据
        $this->refreshOrigin();
        return true;
    }

    /**
     * 验证和过滤数据
     * @param bool $isUpdate 是否更新
     * @return array [$data, $relations, $allow]
     */
    protected function validateAndFilterData(bool $isUpdate): array
    {
        $data     = $this->getData();
        $origin   = $this->getOrigin();
        $allow    = $this->getOption('allow') ?: array_keys($this->getFields());
        $readonly = $this->getOption('readonly');
        $disuse   = $this->getOption('disuse');
        $allow    = array_diff($allow, $disuse, $isUpdate ? $readonly : []);
        $together = $this->getOption('together');

        // 验证数据
        $this->validate($data, $allow);

        $relations = [];
        foreach ($data as $name => &$val) {
            if ($val instanceof Modelable || in_array($name, $together)) {
                $relations[$name] = $val;
                unset($data[$name]);
            } elseif ($val instanceof Collection || (!empty($allow) && !in_array($name, $allow))) {
                unset($data[$name]);
            } elseif ($isUpdate && !$this->isForce() && $this->isNotRequireUpdate($name, $val, $origin)) {
                unset($data[$name]);
            } else {
                $val = $this->setAttrOfWith($name, $val);
            }
        }

        return [$data, $relations, $allow];
    }

    /**
     * 数据检查.
     * @param array $data 数据
     * @param bool  $isUpdate 是否更新
     * @return void
     */
    protected function checkData(array &$data, bool $isUpdate): void
    {
    }

    protected function getDbWhere($where = [])
    {
        $db = $this->db();
        // 检查条件
        if (!empty($where)) {
            $db->where($where);
        } elseif ($this->getKey()) {
            $db->setKey($this->getKey());
        } else {
            $db->where($this->getOrigin());
        }

        if ($this->isForce()) {
            $db->removeOption('soft_delete');
        }

        return $db;
    }

    /**
     * 检查字段是否有更新（主键无需更新）.
     *
     * @param string $name 字段
     * @param mixed $val 值
     * @param array $origin 原始数据
     * @return bool
     */
    protected function isNotRequireUpdate(string $name, $val, array $origin): bool
    {
        return (array_key_exists($name, $origin) && $val === $origin[$name]) || $this->getPk() == $name;
    }

    /**
     * 获取更新数据.
     *
     * @return array
     */
    public function getChangedData(): array
    {
        $data   = $this->getData();
        $origin = $this->getOrigin();
        $change = [];
        foreach ($data as $name => $val) {
            if (!array_key_exists($name, $origin) || $val !== $origin[$name]) {
                $change[$name] = $val;
            }
        }
        return $change;
    }

    /**
     * 判断数据是否有更新.
     *
     * @param string $name 字段
     * @return bool
     */
    public function isChange(string $name): bool
    {
        return $this->getData($name) !== $this->getOrigin($name);
    }

    /**
     * 刷新模型数据.
     *
     * @return static
     */
    public function refresh()
    {
        if ($this->isExists() && $this->getKey()) {
            $data = $this->db()->find($this->getKey())->getData();
            $this->data($data);
        }
        return $this;
    }

    /**
     * 保存多个数据到当前数据对象
     *
     * @param iterable $dataSet 数据
     * @param bool     $replace 是否replace
     *
     * @return Collection
     */
    public static function saveAll(iterable $dataSet, bool $replace = true): Collection
    {
        $result = [];
        $model  = new static;
        $pk     = $model->getPk();
        foreach ($dataSet as $key => $data) {
            $model  = new static;
            if ($replace) {
                $exists = true;
                foreach ((array) $pk as $field) {
                    if (is_string($field) && !isset($data[$field])) {
                        $exists = false;
                    }
                }
            } else {
                $exists = false;
            }

            $model->replace($replace)->exists($exists)->save($data);
            $result[$key] = $model->fetchModel($model);
        }
        return $model->toCollection($result);
    }

    /**
     * 删除模型数据.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->isEmpty() || false === $this->trigger('BeforeDelete')) {
            return false;
        }

        foreach ($this->getData() as $name => $val) {
            if ($val instanceof Model || $val instanceof Collection) {
                $relations[$name] = $val;
            }
        }

        $result = $this->getDbWhere()->delete();

        $this->trigger('AfterDelete');

        if ($result && !empty($relations)) {
            // 删除关联数据
            $this->relationDelete($relations);
        }

        $this->exists(false);
        $this->clear();
        return true;
    }

    /**
     * 写入数据.
     *
     * @param array|object  $data 数据
     * @param array  $allowField  允许字段
     * @param bool   $replace     使用Replace
     * @param string $suffix      数据表后缀
     * @return Modelable
     */
    public static function create(array | object $data, array $allowField = [], bool $replace = false, string $suffix = ''): Modelable
    {
        $model = new static();
        if (!empty($suffix)) {
            $model->setSuffix($suffix);
        }
        $model->allowField($allowField)->replace($replace)->save($data, true);            
        return $model->fetchModel($model);
    }

    /**
     * 更新数据.
     *
     * @param array|object  $data 数据
     * @param mixed  $where       更新条件
     * @param array  $allowField  允许字段
     * @param string $suffix      数据表后缀
     * @param bool   $refresh     是否刷新数据
     * @return Modelable
     */
    public static function update(array | object $data, $where = [], array $allowField = [], string $suffix = '', bool $refresh = false): Modelable
    {
        $model  = new static();
        if (!empty($suffix)) {
            $model->setSuffix($suffix);
        }
        $model->allowField($allowField)->exists(true)->save($data, $where, $refresh);
        return $model->fetchModel($model);
    }

    /**
     * 删除记录.
     *
     * @param mixed $data  主键列表 支持闭包查询条件
     * @param bool  $force 是否强制删除
     * @param array $together 关联删除
     *
     * @return bool
     */
    public static function destroy($data, bool $force = false, array $together = []): bool
    {
        if (empty($data) && 0 !== $data) {
            return false;
        }        
        $db = (new static())->db();

        if (is_array($data) && key($data) !== 0) {
            $db->where($data);
            $data = [];
        } elseif ($data instanceof Closure) {
            $data($db);
            $data = [];
        }

        $resultSet = $db->select((array) $data);

        foreach ($resultSet as $result) {
            $result->force($force)->together($together)->delete();
        }
        return true;
    }

    /**
     * 字段值增长
     *
     * @param string    $field    字段名
     * @param float|int $step     增长值
     * @param int       $lazyTime 延迟时间（秒）
     *
     * @return $this
     */
    public function inc(string $field, float|int $step = 1, int $lazyTime = 0)
    {
        return $this->set($field, new Express('+', $step, $lazyTime));
    }

    /**
     * 字段值减少.
     *
     * @param string    $field    字段名
     * @param float|int $step     增长值
     * @param int       $lazyTime 延迟时间（秒）
     *
     * @return $this
     */
    public function dec(string $field, float|int $step = 1, int $lazyTime = 0)
    {
        return $this->set($field, new Express('-', $step, $lazyTime));
    }

    /**
     * 查询缓存 数据为空不缓存.
     *
     * @param mixed         $key    缓存key
     * @param int|\DateTime $expire 缓存有效期
     * @param string|array  $tag    缓存标签
     *
     * @return $this
     */
    public function setCache($key = true, $expire = null, $tag = null)
    {
        return $this->setOption('cache', [$key, $expire, $tag]);
    }

    /**
     * 允许写入字段.
     *
     * @param array $allow 允许字段
     *
     * @return $this
     */
    public function allowField(array $allow)
    {
        return $this->setOption('allow', $allow);
    }

    /**
     * 动态设置只读字段.
     *
     * @param array $fields 只读字段
     *
     * @return $this
     */
    public function readonly(array $fields)
    {
        return $this->setOption('readonly', $fields);
    }

    /**
     * 强制写入或删除
     *
     * @param bool $force 强制更新
     *
     * @return $this
     */
    public function force(bool $force = true)
    {
        return $this->setOption('force', $force);
    }

    /**
     * 判断数据是否强制写入或删除.
     *
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->getOption('force', false);
    }

    /**
     * 获取属性 支持获取器
     *
     * @param string $name 名称
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 设置数据 支持类型自动转换
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if ($value instanceof Modelable && $bind = $this->getAttrOfBind($this->getOption('bindAttr'), $name)) {
            // 关联属性绑定
            $this->bindRelationAttr($value, $bind);
        } else {
            $this->set($name, $value);
        }
    }

    /**
     * 检测数据对象的值
     *
     * @param string $name 名称
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return !is_null($this->get($name));
    }

    /**
     * 销毁数据对象的值
     *
     * @param string $name 名称
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        $name = $this->getRealFieldName($name);

        $this->setWeakData('data', $name, null);
        $this->setWeakData('get', $name, null);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function __debugInfo()
    {
        return [
            'data'   => $this->getOption('data'),
            'origin' => $this->getOption('origin'),
            'schema' => $this->getOption('schema'),
        ];
    }

    // JsonSerializable
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 序列化模型对象
     * 
     * @return array
     */
    public function __serialize(): array
    {
        $removeKeys = ['invoker', 'db', 'event'];
        return array_diff_key(self::$weakMap[$this], array_flip($removeKeys));
    }

    /**
     * 反序列化模型对象
     * 
     * @param array $data 
     * @return void
     */
    public function __unserialize(array $data) 
    {
        if (!self::$weakMap) {
            self::$weakMap = new WeakMap;
        }

        self::$weakMap[$this] = $data;
        // 重新初始化
        $this->initialize();
    }

    /**
     * 克隆模型实例
     * 
     * @return void
     */
    public function __clone()
    {
        throw new InvalidArgumentException('use $modelObj->clone() replace clone $modelObj');
    }

    // ArrayAccess
    public function offsetSet(mixed $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function offsetGet(mixed $name): mixed
    {
        return $this->get($name);
    }

    public function offsetExists(mixed $name): bool
    {
        return $this->__isset($name);
    }

    public function offsetUnset(mixed $name): void
    {
        $this->__unset($name);
    }
}
