# 控制器 Services 类属性定义规范

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。规范控制器中 `$services` 属性的定义方式，确保构造函数注入的服务有对应的类属性定义和 `@var` 类型提示，便于 IDE 跳转与补全。

**权威规范见 `.crmeb/rules/代码规范.md`**（第二节：控制器 Services 加载规范；第三节：Services 类 DAO 加载规范）。本文不复制规范正文，承载**自动检测与添加的操作流程**（原 `controlleraddService` 技能已合并至此）。

## 核心要求（强约束）

**【必须】** 控制器构造函数中存在服务注入（`$this->services = $services;` 或 `$this->service = $service;`）时，必须在类中定义对应属性（带 `@var` 类型注释），位置在类开头、构造函数之前。

支持三种注入模式（详见权威规则）：单一服务注入（复数形式）、单一服务注入（单数形式）、多服务注入。

---

## 一、自动检测与添加操作流程

### 1.1 检测逻辑

**检查条件（同时满足才处理）：**

1. 构造函数中存在 `$this->services = $services;` **或** `$this->service = $service;` 赋值语句
2. 类中不存在对应的 `protected $services;` **或** `protected $service;` 属性定义

**处理结果：** 在构造函数之前添加带类型提示的属性定义：

```php
// 复数形式
/**
 * @var ServiceClassName
 */
protected $services;

// 或单数形式
/**
 * @var ServiceClassName
 */
protected $service;
```

### 1.2 支持的服务属性命名模式

| 模式 | 属性名 | 示例 |
|------|--------|------|
| 复数形式 | `$services` | `$this->services = $services;` |
| 单数形式 | `$service` | `$this->service = $service;` |
| 多服务形式 | `$xxxServices` | `$this->envServices = $envServices;` |

### 1.3 排除情况（不需要修改）

以下情况**不需要修改**（完整规则见 `.crmeb/rules/代码规范.md` 2.5 节）：

1. **多服务注入**：控制器使用多个服务属性（如 `$envServices`、`$ciServices`），且每个属性都已正确定义
2. **无服务注入**：构造函数中没有注入任何服务（仅 `parent::__construct($app)`）
3. **已定义属性**：控制器已定义 `protected $services;` 或 `protected $service;` 属性（带 `@var` 注释）
4. **混合形式**：参数是单数 `$service`，但赋值给复数属性 `$this->services`（如 `StoreOrder.php`）

### 1.4 处理前后示例

**处理前：**

```php
class SystemRoute extends AuthController
{
    public function __construct(App $app, SystemRouteServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }
}
```

**处理后：**

```php
class SystemRoute extends AuthController
{
    /**
     * @var SystemRouteServices
     */
    protected $services;

    public function __construct(App $app, SystemRouteServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }
}
```

### 1.5 执行步骤

1. **选择目标控制器文件**：指定需要处理的控制器文件路径
2. **检测构造函数**：检查构造函数中是否存在 `$this->services = $services;` 赋值
3. **检查属性定义**：检查类中是否已存在 `protected $services;` 属性定义
4. **自动添加属性**：若检测到服务注入但缺少属性定义，则在构造函数之前添加带类型提示的属性定义
5. **验证结果**：确认属性已正确添加，且类型提示与构造函数参数类型一致

### 1.6 验证规则

**检测正则表达式：**

```regex
// 检测构造函数中的 services 赋值
\$this->services\s*=\s*\$services;

// 检测已存在的属性定义
protected\s+\$services;
```

**类型提取规则：** 从构造函数参数中提取 Service 类名：

```php
// 输入：public function __construct(App $app, SystemRouteServices $services)
// 提取：SystemRouteServices
```

### 1.7 安全检查清单

- [ ] 构造函数中存在 `$this->services = $services;` 赋值
- [ ] 类中不存在 `protected $services;` 属性定义
- [ ] 属性定义已正确添加到构造函数之前
- [ ] 类型提示与构造函数参数类型一致
- [ ] 修改后的代码语法正确

### 1.8 不支持的情况

1. 多个不同类型的 services 属性（需逐个手动定义，参见排除情况 1）
2. 非标准的服务注入方式
3. 匿名类或闭包注入

---

## 二、Services 层 DAO 属性检查

**【必须】** Services 类中使用的 DAO 同样必须在类中定义属性（带 `@var` 注释），完整规范见 `.crmeb/rules/代码规范.md` 第三节。

批量检查命令（查找构造函数中有 `$this->dao = $dao` 但缺少 `protected $dao` 定义的文件）：

```bash
find backend/app/services -name "*.php" -exec sh -c '
    grep -q "\$this->dao =" "{}" && \
    ! grep -q "protected \$dao" "{}" && \
    echo "❌ $(basename "{}")"' \;
```
