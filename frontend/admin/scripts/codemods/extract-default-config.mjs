// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 一次性 codemod：把 mobilePage 组件 script setup 内的
//   const defaultConfig = {...};
// 提升为独立 <script> 块的具名导出（模块级可见），
// 供 mobilePage/index.js 聚合层挂到组件对象上（Vue2 时代经 data().defaultConfig 暴露）。
// 用法：node scripts/codemods/extract-default-config.mjs [--check]
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const dir = path.join(root, 'src/diy/components/mobilePage');
const checkOnly = process.argv.includes('--check');

// 花括号配对（跳过字符串/模板字面量/注释内的括号）
function findStatementEnd(src, startIdx) {
  let depth = 0;
  let i = startIdx;
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
    if (ch === '"' && src.lastIndexOf('\n', i) === -1) {
      // 文件不含双引号字符串（prettier 单引号），防御性跳过
      quote = '"';
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
        // 吃掉结尾分号
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
let skipped = 0;
for (const file of fs.readdirSync(dir).filter((f) => f.endsWith('.vue'))) {
  const full = path.join(dir, file);
  let src = fs.readFileSync(full, 'utf8');

  const m = src.match(/^const defaultConfig = \{/m);
  if (!m) {
    skipped++;
    continue;
  }
  // 已经有具名导出的跳过
  if (/^export const defaultConfig = \{/m.test(src)) {
    skipped++;
    continue;
  }
  const start = m.index;
  const end = findStatementEnd(src, src.indexOf('{', start));
  if (end === -1) {
    console.error(`[跳过] 花括号配对失败: ${file}`);
    skipped++;
    continue;
  }
  const statement = src.slice(start, end);

  let out = src.slice(0, start) + src.slice(end);
  // 清掉提取后留下的多余空行（最多两行）
  out = out.replace(/\n{3,}(?=\S)/g, '\n\n');
  // 在 <script setup> 前插入普通 <script> 块
  const setupTag = out.match(/<script setup>/);
  if (!setupTag) {
    console.error(`[跳过] 未找到 <script setup>: ${file}`);
    continue;
  }
  const insertAt = setupTag.index;
  const block = `<script>\n// 模块级具名导出：DIY 编辑器新增组件时经聚合层挂到组件对象上取默认配置\n// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露）\nexport ${statement}\n</script>\n\n`;
  out = out.slice(0, insertAt) + block + out.slice(insertAt);

  if (!checkOnly) fs.writeFileSync(full, out);
  changed++;
  console.log(`[完成] ${file}`);
}
console.log(`\n处理 ${changed} 个，跳过 ${skipped} 个${checkOnly ? '（check 模式，未写盘）' : ''}`);
