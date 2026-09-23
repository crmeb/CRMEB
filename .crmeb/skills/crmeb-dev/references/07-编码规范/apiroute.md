# API 路由方法命名规范化（snake → camel）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。适用于 `app/api/route/` 用户端路由与控制器方法的命名。

**权威规范见 `.crmeb/rules/代码规范.md`**（路由方法命名强制规范 + 转换规则）。本文承载批量转换的**操作流程**（原 `adminapirouterename` 技能已合并至此）。

## 核心要求（强约束）

- 控制器方法名必须使用**驼峰命名法**（camelCase），禁止下划线命名（`snake_case` → `camelCase`）
- 路由文件与对应控制器方法必须**同步修改**
- 已是驼峰命名或单词形式的方法名无需修改

## ⚠️ 核心原则：路由与控制器必须同步修改

**【强制要求】每次修改必须同时改动两个地方：**
1. **路由文件**（route/*.php）
2. **控制器文件**（controller/**/*.php）

**【绝对禁止】只改路由不改控制器，或只改控制器不改路由！**

### 同步修改示例

```php
// ✅ 正确：同时修改
// 路由文件：Route::get('wechat/code_reply/:id', [ReplyController::class, 'codeReply'])
// 控制器：public function codeReply($id)

// ❌ 错误：只修改了路由
// 路由文件：Route::get('wechat/code_reply/:id', [ReplyController::class, 'codeReply'])
// 控制器：public function code_reply($id) ← 仍然下划线！

// ❌ 错误：只修改了控制器
// 路由文件：Route::get('wechat/code_reply/:id', [ReplyController::class, 'code_reply']) ← 仍然下划线！
// 控制器：public function codeReply($id)
```

## 基础转换规则

**下划线命名 → 小驼峰命名**

| 下划线命名 | 驼峰命名 |
|-----------|---------|
| `get_badge` | `getBadge` |
| `set_status` | `setStatus` |
| `order_list` | `orderList` |
| `get_user_info` | `getUserInfo` |
| `del_file` | `delFile` |
| `open_file` | `openFile` |
| `save_file` | `saveFile` |

## 重要说明

- ✅ 只修改控制器方法名（如 `get_badge` → `getBadge`）
- ❌ 不修改路由 URL 路径（如 `wechat/code_reply/:id` 保持不变）
- ❌ 不修改 `->name()` 路由名称
- ❌ 不修改 `->option()` 配置项
- ❌ 不修改控制器类名
- ⚠️ **路由文件和控制器文件必须同时修改**

## 处理流程

1. **定位**：找到包含下划线方法名的路由文件及对应控制器
2. **转换**：将路由引用的方法名与控制器方法名同步改为驼峰命名
3. **验证**：确认路由与控制器均已更新、无下划线残留
4. **检查**：路由引用的控制器方法确实存在

## 验证命令

### 全局验证（修改前检查）

```bash
# 查找路由文件中引用下划线方法名的定义
grep -rE "'[a-zA-Z]+/[a-z_]+'" backend/app/adminapi/route/*.php backend/modules/*/route/*.php | grep -v "index\|read\|save\|delete\|create\|update\|edit" | head -20

# 查找控制器文件中的下划线方法名
grep -rE "public function [a-z_]+" backend/app/adminapi/controller backend/modules --include="*Controller.php" | grep "_"
```

### 文件级别验证（修改后检查）

```bash
# 检查特定路由文件是否还有下划线方法名（应该为空）
grep -E "'[a-z]+/[a-z_]+'" backend/app/adminapi/route/具体文件.php | grep "_"

# 检查特定控制器文件是否还有下划线方法名（应该为空）
grep -E "public function [a-z_]+" backend/app/adminapi/controller/对应控制器路径.php | grep "_"
```

## ⚠️ 安全检查清单

**每次修改完成后必须逐项检查：**

- [ ] 路由文件已修改（下划线方法名 → 驼峰方法名）
- [ ] 对应的控制器文件已修改（方法名同步更新）
- [ ] 路由文件中该路由无下划线方法名残留
- [ ] 控制器文件中该方法无下划线命名残留
- [ ] 路由引用的控制器方法确实存在

## 注意事项

### ⚠️ 绝对禁止

1. **【禁止】只修改路由文件，不修改控制器文件**
   - 后果：路由调用失败，接口无法访问
2. **【禁止】只修改控制器文件，不修改路由文件**
   - 后果：路由找不到方法，接口无法访问
3. **【禁止】修改路由 URL 路径**
   - URL 路径中的下划线不需要修改（如 `wechat/code_reply/:id` 保持不变）

### ✅ 正确做法

1. **保持路由 URL 不变**：URL 路径中的下划线不需要修改
2. **同步修改**：路由文件和方法文件必须同时修改
3. **测试验证**：修改完成后运行测试确保功能正常
4. **逐项验证**：修改后必须执行安全检查清单
