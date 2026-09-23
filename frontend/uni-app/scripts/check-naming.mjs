#!/usr/bin/env node
/**
 * 命名规范检查（零依赖）。规则与豁免清单见 docs/命名规范.md。
 *
 * 用法：
 *   npm run lint:naming          检查模式：白名单外的违规报错并 exit 1
 *   npm run lint:naming:update   重建存量白名单 scripts/naming-baseline.json
 */
import { readdirSync, statSync, readFileSync, writeFileSync } from 'node:fs';
import { join, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = fileURLToPath(new URL('..', import.meta.url));
const SRC = join(ROOT, 'src');
const BASELINE_FILE = join(ROOT, 'scripts', 'naming-baseline.json');
const UPDATE = process.argv.includes('--update-baseline');

// 豁免前缀（相对 src/）：第三方库与契约冻结层，见规范 §3-5
const EXEMPT_PREFIXES = [
  'subpackage/diyComponents',
  'components/mp-html',
  'components/easy-loadimage',
  'components/zb-code',
  'pages/annex/components/verify',
  'pages/users/components/verify',
  'static/plugin',
  'static/fonts',
];

const VUE_RE = /^[A-Z][a-zA-Z0-9]*$/;
const JS_RE = /^[a-z][a-zA-Z0-9]*$/;
const IMG_RE = /^[a-z0-9]+(-[a-z0-9]+)*$/;
const TOP_DIR_RE = /^[a-z][a-z0-9-]*$/;
const PAGE_DIR_RE = /^[a-z][a-z0-9_]*$/;
const PASCAL_DIR_RE = /^[A-Z][a-zA-Z0-9]*$/;
const IMG_EXT = new Set(['.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp', '.ico']);
// pages 树中的角色目录：其内部按文件规则校验，不再按页面目录名规则校验
const ROLE_DIRS = new Set(['components', 'component', 'static']);

const violations = new Map(); // relpath -> 规则说明

const rel = (p) => relative(ROOT, p).split(sep).join('/');
const isExempt = (relPath) =>
  EXEMPT_PREFIXES.some((pre) => relPath === `src/${pre}` || relPath.startsWith(`src/${pre}/`));
const addViolation = (relPath, rule) => {
  if (!isExempt(relPath)) violations.set(relPath, rule);
};

function walk(dir, files, dirs) {
  for (const name of readdirSync(dir)) {
    const p = join(dir, name);
    if (statSync(p).isDirectory()) {
      dirs.push(p);
      walk(p, files, dirs);
    } else {
      files.push(p);
    }
  }
}

const files = [];
const dirs = [];
walk(SRC, files, dirs);

for (const f of files) {
  const relPath = rel(f);
  const name = relPath.split('/').pop();
  const ext = name.slice(name.lastIndexOf('.')).toLowerCase();
  if (name.includes('.min.')) continue; // 压缩版第三方库
  if (ext === '.vue' && name !== 'index.vue' && !VUE_RE.test(name.replace(/\.vue$/, ''))) {
    addViolation(relPath, '.vue 组件须 PascalCase（或 index.vue）');
  } else if ((ext === '.js' || ext === '.mjs') && !JS_RE.test(name.replace(/\.(m?js)$/, ''))) {
    addViolation(relPath, 'js 模块须 camelCase 或全小写单词');
  } else if (IMG_EXT.has(ext)) {
    const stem = name.slice(0, name.length - ext.length);
    if (!IMG_RE.test(stem) || !/[a-z]/.test(stem)) addViolation(relPath, '图片须 kebab-case 全小写且含字母（禁下划线/大写/拼音/纯数字）');
  }
}

for (const d of dirs) {
  const relPath = rel(d);
  const segs = relPath.split('/'); // ['src', ...]
  const name = segs[segs.length - 1];
  if (segs.length === 2) {
    if (!TOP_DIR_RE.test(name)) addViolation(relPath, 'src 顶层目录须全小写或 kebab-case');
  } else if (segs[1] === 'pages') {
    const inner = segs.slice(2, -1); // 该目录的所有祖先段
    if (!inner.some((s) => ROLE_DIRS.has(s)) && !PAGE_DIR_RE.test(name)) {
      addViolation(relPath, 'pages/ 页面目录须 snake_case 或全小写单词');
    }
  } else if (segs[1] === 'components' && segs.length === 3 && !PASCAL_DIR_RE.test(name)) {
    addViolation(relPath, 'components/ 组件目录须 PascalCase');
  }
}

const sorted = [...violations.keys()].sort();
let baseline = { generatedAt: null, entries: [] };
try {
  baseline = JSON.parse(readFileSync(BASELINE_FILE, 'utf8'));
} catch {
  /* 首次运行，白名单为空 */
}
const baselineSet = new Set(baseline.entries);

if (UPDATE) {
  writeFileSync(BASELINE_FILE, `${JSON.stringify({ generatedAt: new Date().toISOString(), entries: sorted }, null, '\t')}\n`);
  console.log(`白名单已重建：${sorted.length} 项存量违规（原 ${baseline.entries.length} 项）→ scripts/naming-baseline.json`);
  process.exit(0);
}

const fresh = sorted.filter((p) => !baselineSet.has(p));
const stale = baseline.entries.filter((p) => !violations.has(p));
if (fresh.length) {
  console.error(`✖ 命名规范违规 ${fresh.length} 处（存量白名单之外，规则见 docs/命名规范.md）：`);
  for (const p of fresh) console.error(`  ${p}  — ${violations.get(p)}`);
  process.exit(1);
}
console.log(`✔ 命名规范检查通过（白名单存量违规 ${sorted.length} 项）。`);
if (stale.length) {
  console.log(`提示：${stale.length} 项白名单条目已失效，可运行 npm run lint:naming:update 清理。`);
}
