// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 一次性 codemod（extract-default-config 的后续）：
// 把 mobilePage 组件独立 <script> 块的
//   export const defaultConfig = { ... timestamp: props.num ... };
// 改为工厂函数
//   export const getDefaultConfig = (num) => ({ ... timestamp: num ... });
// 并在 <script setup> 的 defineProps 之后重建实例级
//   const defaultConfig = getDefaultConfig(props.num);
// 用法：node scripts/codemods/default-config-factory.mjs
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const dir = path.join(root, 'src/diy/components/mobilePage');

function findStatementEnd(src, openBraceIdx) {
  let depth = 0;
  let i = openBraceIdx;
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
    if (ch === '{') depth++;
    if (ch === '}') {
      depth--;
      if (depth === 0) {
        let end = i + 1;
        if (src[end] === ';') end++;
        return end;
      }
    }
    i++;
  }
  return -1;
}

let changed = 0;
for (const file of fs.readdirSync(dir).filter((f) => f.endsWith('.vue'))) {
  const full = path.join(dir, file);
  let src = fs.readFileSync(full, 'utf8');

  const m = src.match(/^export const defaultConfig = \{/m);
  if (!m) continue;

  const openBrace = src.indexOf('{', m.index);
  const end = findStatementEnd(src, openBrace);
  if (end === -1) {
    console.error(`[跳过] 配对失败: ${file}`);
    continue;
  }

  // 1) 导出改工厂：头/尾改写 + props.num -> num（仅块内）
  let block = src.slice(m.index, end);
  block = block.replace(/^export const defaultConfig = \{/, 'export const getDefaultConfig = (num) => ({');
  block = block.replace(/props\.num/g, 'num');
  block = block.replace(/\};\s*$/, '});');

  // 2) 注释同步更新
  const comment =
    '// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂\n// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）';
  src = src.replace(/\/\/ 模块级具名导出：[^\n]*\n\/\/ （原 Vue2[^\n]*\n/, `${comment}\n`);

  src = src.slice(0, m.index) + block + src.slice(end);

  // 3) defineProps 之后重建实例级 defaultConfig
  if (/^const defaultConfig = getDefaultConfig/m.test(src)) {
    console.error(`[跳过] 已有实例级重建: ${file}`);
    continue;
  }
  const dp = src.match(/const props = defineProps\(/m);
  if (!dp) {
    console.error(`[跳过] 未找到 const props = defineProps: ${file}`);
    continue;
  }
  const dpEnd = findStatementEnd(src, src.indexOf('(', dp.index));
  if (dpEnd === -1) {
    console.error(`[跳过] defineProps 配对失败: ${file}`);
    continue;
  }
  const insert = `\n// 实例级默认配置（timestamp 取当前实例 num）\nconst defaultConfig = getDefaultConfig(props.num);`;
  src = src.slice(0, dpEnd) + insert + src.slice(dpEnd);

  fs.writeFileSync(full, src);
  changed++;
  console.log(`[完成] ${file}`);
}
console.log(`\n处理 ${changed} 个`);
