// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 一次性修复：mobileConfig 批次 45 个文件的 import 语句被误写在
// <script setup> 块外（SFC 编译器忽略块外裸文本，编译不报错但导入无效）。
// 修复：把块外区域的全部 import 语句（含一行多句）拆分后移入 <script setup> 块首。
// 用法：node scripts/codemods/fix-dangling-imports.mjs
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

function walk(dir, out = []) {
  for (const name of fs.readdirSync(dir)) {
    const full = path.join(dir, name);
    const st = fs.statSync(full);
    if (st.isDirectory()) walk(full, out);
    else if (name.endsWith('.vue')) out.push(full);
  }
  return out;
}

// 把块外区域文本按引号感知方式拆成单条 import 语句
function splitStatements(text) {
  const stmts = [];
  let cur = '';
  let quote = null;
  for (let i = 0; i < text.length; i++) {
    const ch = text[i];
    if (quote) {
      cur += ch;
      if (ch === '\\') {
        cur += text[++i] ?? '';
        continue;
      }
      if (ch === quote) quote = null;
      continue;
    }
    if (ch === "'" || ch === '"' || ch === '`') {
      quote = ch;
      cur += ch;
      continue;
    }
    if (ch === ';') {
      if (cur.trim()) stmts.push(cur.trim());
      cur = '';
      continue;
    }
    cur += ch;
  }
  if (cur.trim()) stmts.push(cur.trim());
  return stmts;
}

let fixed = 0;
for (const file of walk(path.join(root, 'src'))) {
  let src = fs.readFileSync(file, 'utf8');
  const setupIdx = src.indexOf('<script setup>');
  if (setupIdx === -1) continue;
  const head = src.slice(0, setupIdx);
  if (!/^import /m.test(head)) continue;

  // 块外区域：取 </template>（或文件头）到 <script setup> 之间
  const tplEnd = head.lastIndexOf('</template>');
  const regionStart = tplEnd === -1 ? 0 : tplEnd + '</template>'.length;
  const region = head.slice(regionStart);
  const rest = head.slice(0, regionStart);

  // 区域里应当只有 import 语句与空白/换行；逐行收集含 import 的行
  const lines = region.split('\n');
  const importLines = [];
  const otherLines = [];
  let inImport = false;
  for (const line of lines) {
    const t = line.trim();
    if (!t) continue;
    if (inImport) {
      // 续行：上一行以 from 结尾等
      importLines[importLines.length - 1] += ' ' + t;
      if (/;$/.test(t) || /from\s+'[^']+'$/.test(t)) inImport = false;
      continue;
    }
    if (t.startsWith('import ')) {
      importLines.push(t);
      if (/;$/.test(t) || /from\s+'[^']+'$/.test(t)) inImport = false;
      else inImport = true; // 语句被折行
      continue;
    }
    otherLines.push(t);
  }

  const stmts = [];
  for (const line of importLines) stmts.push(...splitStatements(line));
  const importBlock = stmts
    .map((s) => (s.endsWith(';') ? s : s + ';'))
    .join('\n');

  // 移回 <script setup> 块首
  const insertAt = setupIdx + '<script setup>'.length;
  src = rest + '\n' + src.slice(setupIdx, insertAt) + '\n' + importBlock + '\n' + src.slice(insertAt);

  fs.writeFileSync(file, src);
  fixed++;
  console.log(`[修复] ${path.relative(root, file)}${otherLines.length ? '（注意：区域含非 import 残留 ' + otherLines.length + ' 行，已保留在块外原处）' : ''}`);
}
console.log(`\n修复 ${fixed} 个文件`);
