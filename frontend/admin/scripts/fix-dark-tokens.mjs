#!/usr/bin/env node
/**
 * 深色模式适配批量脚本：把 pages/ 与 components/ 下 Vue 文件中的
 * 硬编码深色文字/浅灰边框替换为 --prev-* 令牌。
 *
 * 只替换单一颜色声明行，不做跨行/嵌套推断；预览语义白底（#fff）不在本脚本范围。
 * 已有 var(--prev-*) 的行跳过。幂等：重复运行无副作用。
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../src');
const targets = ['pages', 'components'];
// 预览/画布语义目录：白底与配套深色文字是业务语义，整体跳过
const skips = [
  'pages/diy', // 无此目录，防误配
  'plugin/kefu/mobile',
  'plugin/kefu/appChat',
  'plugin/wechat_mp/pages',
  'diy/',
];

const colorMap = [
  [/color:\s*#303133\b/g, 'color: var(--prev-color-text-regular)'],
  [/color:\s*#17233d\b/g, 'color: var(--prev-color-text-regular)'],
  [/color:\s*#333333\b/g, 'color: var(--prev-color-text-regular)'],
  [/color:\s*#333\b(?![0-9a-fA-F])/g, 'color: var(--prev-color-text-regular)'],
  [/color:\s*#606266\b/g, 'color: var(--prev-color-text-primary)'],
  [/color:\s*#909399\b/g, 'color: var(--prev-color-text-secondary)'],
  [/border(-color)?:\s*1px solid #eee\b/g, 'border$1: 1px solid var(--prev-border-color-lighter)'],
  [/border(-color)?:\s*1px solid #f2f2f2\b/g, 'border$1: 1px solid var(--prev-border-color-lighter)'],
  [/border(-color)?:\s*1px solid #f5f5f5\b/g, 'border$1: 1px solid var(--prev-border-color-lighter)'],
  [/border-bottom:\s*1px solid #eee\b/g, 'border-bottom: 1px solid var(--prev-border-color-lighter)'],
  [/border-bottom:\s*1px solid #f2f2f2\b/g, 'border-bottom: 1px solid var(--prev-border-color-lighter)'],
  [/border-top:\s*1px solid #eee\b/g, 'border-top: 1px solid var(--prev-border-color-lighter)'],
];

function* walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) yield* walk(full);
    else if (entry.name.endsWith('.vue')) yield full;
  }
}

let fileCount = 0;
let lineCount = 0;
for (const dir of targets) {
  for (const file of walk(path.join(root, dir))) {
    const rel = path.relative(root, file).replaceAll('\\', '/');
    if (skips.some((s) => rel.includes(s))) continue;
    const src = fs.readFileSync(file, 'utf8');
    let out = src;
    for (const [re, to] of colorMap) out = out.replace(re, to);
    if (out !== src) {
      fs.writeFileSync(file, out);
      fileCount++;
      lineCount += src.split('\n').filter((l, i) => l !== out.split('\n')[i]).length;
    }
  }
}
console.log(`files changed: ${fileCount}`);
