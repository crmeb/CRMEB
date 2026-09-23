// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// 一次性修复脚本：修复 default-config-factory.mjs 因 stale offset 与
// defineProps 括号配对两处 bug 对 mobilePage 40 个组件造成的写坏。
// 修复策略（按可观测的系统性损伤特征）：
//   A. 实例级 const 被插进 defineProps(...) 调用内部（结尾特征 `...props.num););`）→ 拆回
//   B. <script> 块区域（注释与导出拼接、块尾垃圾）→ 用完好的工厂块整体重建
// 用法：node scripts/codemods/repair-default-config.mjs
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const dir = path.join(root, 'src/diy/components/mobilePage');

// 从 openIdx（任一括号）开始做 全类型括号配对，返回配平后位置（含结尾分号）
function findStatementEnd(src, openIdx) {
  const pair = { '}': '{', ')': '(', ']': '[' };
  const open = src[openIdx];
  const close = Object.keys(pair).find((k) => pair[k] === open);
  const stack = [];
  let i = openIdx;
  let quote = null;
  while (i < src.length) {
    const ch = src[i];
    const next = src[i + 1];
    if (quote) {
      if (ch === '\\') {
        i += 2;
        continue;
      }
      if (ch === quote) quote = null;
      i++;
      continue;
    }
    if (ch === "'" || ch === '`') {
      quote = ch;
      i++;
      continue;
    }
    if (ch === '/' && next === '/') {
      i = src.indexOf('\n', i);
      if (i === -1) return -1;
      continue;
    }
    if (ch === '/' && next === '*') {
      i = src.indexOf('*/', i + 2);
      if (i === -1) return -1;
      i += 2;
      continue;
    }
    if (ch === '{' || ch === '(' || ch === '[') stack.push(ch);
    if (ch === '}' || ch === ')' || ch === ']') {
      stack.pop();
      if (stack.length === 0) {
        let end = i + 1;
        if (src[end] === ';') end++;
        return end;
      }
    }
    i++;
  }
  return -1;
}

const CONST_LINE = 'const defaultConfig = getDefaultConfig(props.num);';
const CONST_BLOCK = `\n// 实例级默认配置（timestamp 取当前实例 num）\n${CONST_LINE}`;
const COMMENT =
  '// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂\n' +
  '// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）';

let fixed = 0;
for (const file of fs.readdirSync(dir).filter((f) => f.endsWith('.vue'))) {
  const full = path.join(dir, file);
  let src = fs.readFileSync(full, 'utf8');
  const bad = src.includes('getDefaultConfig');
  if (!bad) continue;
  let touched = false;

  // A. 拆开被插进 defineProps 调用内部的实例级 const（特征：props.num););）
  if (src.includes(`${CONST_LINE});`)) {
    // 还原 defineProps 的闭合 `);`，const 行稍后统一重插
    src = src.replace(`\n// 实例级默认配置（timestamp 取当前实例 num）\n${CONST_LINE});`, ');');
    touched = true;
  }

  // B. 重建 <script> 块：定位完好的工厂块
  const bm = src.match(/^export const getDefaultConfig = \(num\) => \(\{/m);
  if (bm) {
    const open = src.indexOf('(', src.indexOf('=>', bm.index));
    const blockEnd = findStatementEnd(src, open);
    if (blockEnd !== -1) {
      const block = src.slice(bm.index, blockEnd);
      // 块区域范围：其前最近的 <script> 标签 → 其后最近的 </script>
      const tagStart = src.lastIndexOf('<script>', bm.index);
      const tagEnd = src.indexOf('</script>', blockEnd);
      if (tagStart !== -1 && tagEnd !== -1) {
        const rebuilt = `<script>\n${COMMENT}\n${block}\n</script>`;
        src = src.slice(0, tagStart) + rebuilt + src.slice(tagEnd + '</script>'.length);
        touched = true;
      }
    }
  }

  // C. 实例级 const 统一重插到 defineProps 语句之后（此时已能正确配平）
  if (touched && !src.includes(CONST_LINE)) {
    const dp = src.match(/const props = defineProps\(/);
    if (dp) {
      const dpEnd = findStatementEnd(src, src.indexOf('(', dp.index));
      if (dpEnd !== -1) {
        src = src.slice(0, dpEnd) + CONST_BLOCK + src.slice(dpEnd);
      }
    }
  }

  if (touched) {
    fs.writeFileSync(full, src);
    fixed++;
    console.log(`[修复] ${file}`);
  }
}
console.log(`\n修复 ${fixed} 个`);
