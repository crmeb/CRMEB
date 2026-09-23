---
name: 邮件格式
description: 用 builtin.crmeb_email_send 发送邮件时生成美观专业的 HTML 正文：说明系统邮件按 HTML 渲染的事实、提供邮件客户端兼容的排版规范与可复用模板，覆盖数据报告、通知、简讯等场景。
status: 1
---

# 邮件格式

凡是调用 `builtin.crmeb_email_send` 发送邮件（对话中代发、自动化任务推送），邮件正文一律按本技能规则编写，目标是收件人打开即读、美观专业。

## 一、核心事实：正文按 HTML 渲染

系统邮件渠道以 `text/html` 方式发送，**不会把 Markdown 转成 HTML**：

- 传 Markdown 源码，收件人会看到 `#`、`**`、`|---|` 等原始符号，禁止传 Markdown；
- 纯文本中的换行符 `\n` 在邮件里**不会换行**，段落与换行必须用 `<p>`、`<br>` 表达；
- 因此 `content` 参数必须传入**邮件客户端兼容的简单 HTML**。

## 二、HTML 排版规范（美化重点）

邮件客户端兼容性优先于视觉效果，遵守以下规则：

- **只用内联样式**：样式全部写在元素的 `style` 属性上，不使用 `<style>` 标签、外部 CSS、class（多数客户端会剥离）；
- **结构只用简单标签**：`<div>`、`<p>`、`<h3>`、`<h4>`、`<table>`、`<tr>`、`<th>`、`<td>`、`<span>`、`<strong>`、`<a>`、`<hr>`、`<ol>`、`<li>`；禁止 `<script>`、表单、复杂嵌套布局；
- **数据一律用表格**：多列数据用 `<table>`，表头加浅色底（如 `background:#f5f7fa`），单元格加 `padding:8px 12px; border:1px solid #e5e7eb`，数字右对齐更易读；
- **留白与层级**：段落间距用 `margin`（如 `margin:0 0 12px`），重点数字用 `font-size:18px; font-weight:700` 突出，不要整段加粗；
- **配色克制**：全邮件不超过 2 种强调色，可用主题蓝 `#1890ff` 做标题或关键数字点缀，正文用深灰 `#333`，辅助说明用浅灰 `#999`；负向指标（下跌、失败）用红 `#f5222d`，正向用绿 `#52c41a`；
- **不用外链图片**：图片类内容用文字描述，不引用外部图片地址（易被拦截不显示）；
- **宽度自适应**：最外层用 `<div style="max-width:640px; margin:0 auto; font-family:'Microsoft YaHei','PingFang SC',sans-serif; color:#333; font-size:14px; line-height:1.8">` 包裹全部内容。

## 三、邮件结构（按场景组织）

所有邮件遵循「结论先行、层次分明、落款完整」：

1. **标题区**：`<h3>` 大标题点明邮件主题，可带日期副标题（`<span style="color:#999; font-size:12px">`）；
2. **核心摘要**：开头 2-3 句话给出最重要的结论或要点，让用户 10 秒内抓住重点；关键数字加粗放大；
3. **正文主体**：
   - 数据报告类 → 摘要要点 + 数据表格（表格上方一句话说明口径与时间范围）；
   - 通知提醒类 → 事项说明 + 时间/操作指引（步骤用有序列表 `<ol><li>`）；
   - 简讯类 → 3 段以内短文，不必套模板。
4. **落款区**：`<hr>` 分隔后注明真实数据来源及本次系统提示词注入的「当前系统时间」作为生成时间；业务数据的发生时间不能冒充生成时间；
5. **时间占位符禁止原样发送**：骨架中的 `YYYY-MM-DD HH:mm` 必须替换为本次系统时间；不能获得系统时间时不要推断旧记录发生时间为今天，省略生成时间并如实说明无法确认；
6. 涉及金额、数量等关键数字，必须与工具查询结果一致，禁止编造或凑整。

## 四、可复用骨架（数据报告类）

直接以此骨架为基础填充内容，按需增删区块，不要输出多余解释：

```html
<div style="max-width:640px; margin:0 auto; font-family:'Microsoft YaHei','PingFang SC',sans-serif; color:#333; font-size:14px; line-height:1.8">
  <h3 style="margin:0 0 4px; font-size:20px;">邮件标题</h3>
  <p style="margin:0 0 16px; color:#999; font-size:12px;">统计口径：昨日（M月D日 00:00 - 24:00）</p>
  <p style="margin:0 0 12px;">核心摘要：一句话结论，关键数字 <strong style="color:#1890ff; font-size:18px;">1,234</strong> 突出显示。</p>
  <h4 style="margin:16px 0 8px;">明细数据</h4>
  <table style="width:100%; border-collapse:collapse; font-size:13px;">
    <tr style="background:#f5f7fa;">
      <th style="padding:8px 12px; border:1px solid #e5e7eb; text-align:left;">指标</th>
      <th style="padding:8px 12px; border:1px solid #e5e7eb; text-align:right;">数值</th>
    </tr>
    <tr>
      <td style="padding:8px 12px; border:1px solid #e5e7eb;">指标名称</td>
      <td style="padding:8px 12px; border:1px solid #e5e7eb; text-align:right;">1,234.56</td>
    </tr>
  </table>
  <p style="margin:16px 0 0; color:#f5222d; font-size:12px;">异常提示：如有异常数据在此用一行说明。</p>
  <hr style="border:none; border-top:1px solid #eee; margin:20px 0 8px;">
  <p style="margin:0; color:#999; font-size:12px;">数据来源：CRMEB 管理后台 · 生成时间：<!-- 格式占位，发送前必须替换为真实当前日期时间（以系统提示词注入的「当前系统时间」为准） -->YYYY-MM-DD HH:mm</p>
</div>
```

## 五、主题与发送规范

- **主题**：中文、含内容概括与日期，如「每日经营数据报告（09-07）」「订单异常提醒：3 笔待处理」，不超过 30 字；
- **收件邮箱未知时，先用 `crmeb_ask_user` 询问**，禁止凭猜测填写；
- 对话场景在 `builtin.crmeb_email_send` 调用的 `display` 中概括收件人、主题和内容，等待用户通过页面确认卡授权，不重复索取口头同意；无人值守任务只按任务规定的送达目标和工具权限执行；
- 发送失败时按工具返回的原因排查（如邮件驱动未配置），**禁止用相同参数原样重试**；
- 正文总量控制在 20000 字符以内（超出会被截断），内容精炼、表格行数适度，明细过多时只呈现 Top 5-10 并注明「完整数据请到后台查看」；
- 邮件中不出现接口内部字段名、密钥、令牌及与主题无关的个人信息。

## 六、正文纪律（硬性红线）

邮件正文只承载**业务结果与数据**，收件人是业务人员而非运维人员：

1. **禁止写入排障信息**：发送过程的问题排查、失败原因分析、重试说明、配置诊断（如「某授权码为空」「驱动未启用」之类内容）一律不得出现在邮件正文；失败时的原因说明只回复给对话或记入运行记录，重新发送成功后邮件正文只包含业务数据；
2. **禁止臆断系统配置状态**：通过 `builtin.crmeb_driver_list` 查看配置时，密码类字段 `value` 恒为掩码 `******` 或空，仅 `has_value` 字段表示是否已配置——**不得据 `value` 为掩码/空断言「未配置/为空」**；网络瞬时失败（如连接失败、超时）也不代表配置有问题，禁止在正文输出配置诊断结论；
3. **统计口径如实标注**：正文标题与口径说明必须与数据来源工具的真实能力一致；工具不支持按时间过滤时，如实标注实际口径（如「当前全量用户」），禁止把全量/其他口径数据冒充任务要求的时间口径（如「昨日」）。
