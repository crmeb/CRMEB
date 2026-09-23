// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// Vue2 -> Vue3 一次性批量语法转换（codemod）
// 用法：node scripts/codemods/transform.mjs [--dry]
// 仅做文本级保守转换，转换不了的记录到 scripts/codemods/report.json 供人工处理。
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, URL } from 'node:url';

const ROOT = fileURLToPath(new URL('../..', import.meta.url));
const SRC = path.join(ROOT, 'src');
const DRY = process.argv.includes('--dry');

const FILTER_NAMES = [
  'liveReviewStatusFilter',
  'liveStatusFilter',
  'formatDate',
  'broadcastType',
  'filterClose',
  'broadcastDisplayType',
  'filterEmpty',
  'userType',
  'sourceType',
];
const ASSET_EXT = /\.(png|jpe?g|gif|svg|webp|ico|bmp|mp3|mp4|ttf|woff2?|eot|json)$/i;
const EP_IMPORT_MAP = {
  Message: 'ElMessage',
  MessageBox: 'ElMessageBox',
  Loading: 'ElLoading',
  Notification: 'ElNotification',
};

const report = { files: 0, changed: 0, counts: {}, issues: [] };
const issue = (type, file, detail) => {
  report.counts[type] = (report.counts[type] || 0) + 1;
  report.issues.push({ type, file, detail: String(detail).slice(0, 300) });
};

// ---------------- 通用工具 ----------------
// 提取平衡括号的实参（处理嵌套 () [] {} 与字符串）
function parseCallArgs(src, openIdx) {
  let depth = 0;
  let quote = null;
  const args = [];
  let cur = '';
  for (let i = openIdx; i < src.length; i++) {
    const c = src[i];
    if (quote) {
      cur += c;
      if (c === quote && src[i - 1] !== '\\') quote = null;
      continue;
    }
    if (c === '"' || c === "'" || c === '`') {
      quote = c;
      cur += c;
      continue;
    }
    if (i === openIdx) {
      // 调用自身的起始括号不计入参数
      depth = 1;
      continue;
    }
    if (c === '(' || c === '[' || c === '{') {
      depth++;
      cur += c;
      continue;
    }
    if (c === ')' || c === ']' || c === '}') {
      depth--;
      if (depth === 0 && c === ')') {
        if (cur.trim()) args.push(cur.trim());
        return { closeIdx: i, args };
      }
      cur += c;
      continue;
    }
    if (c === ',' && depth === 1) {
      args.push(cur.trim());
      cur = '';
      continue;
    }
    cur += c;
  }
  return null;
}

// ---------------- 模板转换 ----------------
function transformSlotSyntax(tpl, file) {
  let out = tpl;
  let guard = 0;
  let changed = true;
  while (changed && guard++ < 20) {
    changed = false;
    const tagRe = /<([a-zA-Z][\w-]*)((?:"[^"]*"|'[^']*'|[^>"'])*?)(\/?)>/g;
    let m;
    while ((m = tagRe.exec(out))) {
      const [full, tagName, attrsRaw, selfClose] = m;
      const attrStr = attrsRaw + '';
      const slotM = attrStr.match(/\sslot="([^"]+)"/);
      const scopeM = attrStr.match(/\sslot-scope="([^"]+)"/);
      if (!slotM && !scopeM) continue;
      let newAttrs = attrStr.replace(/\sslot="[^"]+"/, '').replace(/\sslot-scope="[^"]+"/, '');
      let replacement;
      if (tagName === 'template') {
        const slotName = slotM ? slotM[1] : 'default';
        replacement = `<template${newAttrs}${slotName === 'default' && !scopeM ? '' : ` #${slotName}`}${
          scopeM ? `="${scopeM[1]}"` : ''
        }>`;
      } else if (selfClose) {
        const slotName = slotM ? slotM[1] : 'default';
        replacement = `<template #${slotName}${scopeM ? `="${scopeM[1]}"` : ''}><${tagName}${newAttrs}/></template>`;
      } else {
        // 需要找匹配的闭合标签并包裹
        const closeRe = new RegExp(`<\\/?${tagName}\\b[^>]*>`, 'g');
        closeRe.lastIndex = m.index + full.length;
        let depth = 1;
        let close = null;
        let cm;
        while ((cm = closeRe.exec(out))) {
          const t = cm[0];
          if (t.startsWith('</')) {
            depth--;
            if (depth === 0) {
              close = cm;
              break;
            }
          } else if (!t.endsWith('/>')) {
            depth++;
          }
        }
        if (!close) {
          issue('slot-wrap-failed', file, full);
          continue;
        }
        const slotName = slotM ? slotM[1] : 'default';
        const innerStart = m.index + full.length;
        const innerEnd = close.index;
        const openTag = `<${tagName}${newAttrs}>`;
        const closeTag = close[0];
        const wrapped =
          `<template #${slotName}${scopeM ? `="${scopeM[1]}"` : ''}>` +
          openTag +
          out.slice(innerStart, innerEnd) +
          closeTag +
          '</template>';
        out = out.slice(0, m.index) + wrapped + out.slice(close.index + close[0].length);
        changed = true;
        guard++;
        break; // 重新扫描（索引已变化）
      }
      out = out.slice(0, m.index) + replacement + out.slice(m.index + full.length);
      changed = true;
      break; // 重新扫描
    }
  }
  return out;
}

function transformSync(tpl) {
  let out = tpl;
  // el-dialog / el-drawer 的 :visible.sync -> v-model（Element Plus 使用 modelValue）
  out = out.replace(/<(el-dialog|el-drawer)\b[^>]*>/gs, (tag) =>
    tag.replace(/:visible\.sync="([^"]*)"/g, 'v-model="$1"'),
  );
  // 其余 :xxx.sync="yyy" -> v-model:xxx="yyy"
  out = out.replace(/:([\w-]+)\.sync="([^"]*)"/g, 'v-model:$1="$2"');
  return out;
}

function transformNative(tpl) {
  return tpl.replace(/(@[\w.]+)\.native\b/g, '$1').replace(/v-on:(\w+)\.native\b/g, 'v-on:$1');
}

function transformFiltersInTpl(tpl, file) {
  return tpl.replace(/\{\{([\s\S]*?)\}\}/g, (full, inner) => {
    if (!/\|/.test(inner)) return full;
    // 按顶层单竖线切分（忽略 ||）
    const parts = [];
    let cur = '';
    let quote = null;
    let depth = 0;
    for (let i = 0; i < inner.length; i++) {
      const c = inner[i];
      if (quote) {
        cur += c;
        if (c === quote && inner[i - 1] !== '\\') quote = null;
        continue;
      }
      if (c === '"' || c === "'" || c === '`') {
        quote = c;
        cur += c;
        continue;
      }
      if (c === '(' || c === '[' || c === '{') depth++;
      if (c === ')' || c === ']' || c === '}') depth--;
      if (c === '|' && depth === 0 && inner[i + 1] !== '|' && inner[i - 1] !== '|') {
        parts.push(cur.trim());
        cur = '';
        continue;
      }
      cur += c;
    }
    parts.push(cur.trim());
    if (parts.length < 2) return full;
    let expr = parts[0];
    for (let i = 1; i < parts.length; i++) {
      const fname = parts[i];
      if (!/^[a-zA-Z_$][\w$]*$/.test(fname)) {
        issue('tpl-filter-unknown', file, full);
        return full;
      }
      if (FILTER_NAMES.includes(fname)) {
        expr = `$filters.${fname}(${expr})`;
      } else {
        // 组件局部 filters：转为同名函数直接调用（迁移 script setup 时把 filters 改为普通函数）
        issue('tpl-filter-local', file, `${fname} <- ${full.replace(/\n/g, ' ').slice(0, 120)}`);
        expr = `${fname}(${expr})`;
      }
    }
    return `{{ ${expr} }}`;
  });
}

function transformTplAttrHelpers(tpl, file) {
  let out = tpl;
  // el-submenu -> el-sub-menu
  out = out.replace(/<\/?el-submenu\b/g, (t) => t.replace('el-submenu', 'el-sub-menu'));
  // el-link :underline="false" -> underline="never"
  out = out.replace(/:underline="false"/g, 'underline="never"');
  // 检测项：template v-for 内 key 未上移 / v-for 与 v-if 同标签
  const tagRe = /<[a-zA-Z][\w-]*(?:"[^"]*"|'[^']*'|[^>"'])*>/g;
  let m;
  while ((m = tagRe.exec(out))) {
    const t = m[0];
    if (/<template\b[^>]*v-for/.test(t)) issue('tpl-template-vfor', file, t);
    if (/v-for=/.test(t) && /v-if=/.test(t)) issue('tpl-vfor-vif', file, t);
    if (/\benter-class|\bleave-class\b/.test(t)) issue('tpl-transition-class', file, t);
  }
  return out;
}

// ---------------- 样式转换 ----------------
function transformStyle(css, file) {
  let out = css;
  // ::v-deep / /deep/ 支持开头与选择器中间位置，逐个逗号分段包裹
  out = out
    .replace(/(^|[\s,>~+])::v-deep\s*([^{,]+?)\s*(?=[,{])/g, (m, pre, sel) => `${pre}:deep(${sel.trim()})`)
    .replace(/(^|[\s,>~+])\/deep\/\s*([^{,]+?)\s*(?=[,{])/g, (m, pre, sel) => `${pre}:deep(${sel.trim()})`);
  // webpack tilde 前缀
  out = out.replace(/@import\s+["']~/g, "@import '");
  const leftover = out.match(/::v-deep|\/deep\//g);
  if (leftover) issue('style-deep-leftover', file, `${leftover.length} 处`);
  return out;
}

// ---------------- 脚本转换 ----------------
function transformSetDelete(code, file) {
  let out = code;
  let from = 0;
  const inLineComment = (idx) => {
    const lineStart = out.lastIndexOf('\n', idx) + 1;
    const before = out.slice(lineStart, idx);
    return before.includes('//');
  };
  // 支持 this.$set / that.$set / self.$set 等任意接收者别名
  while (true) {
    const re = /\b(\w+)\.\$set\(/g;
    re.lastIndex = from;
    const m = re.exec(out);
    if (!m) break;
    const idx = m.index;
    const callLen = m[0].length;
    if (inLineComment(idx)) {
      from = idx + callLen;
      continue;
    }
    const parsed = parseCallArgs(out, idx + callLen - 1);
    if (!parsed) break;
    if (parsed.args.length === 3) {
      const rep = `${parsed.args[0]}[${parsed.args[1]}] = ${parsed.args[2]}`;
      out = out.slice(0, idx) + rep + out.slice(parsed.closeIdx + 1);
      from = idx + rep.length;
    } else {
      issue('set-args', file, out.slice(idx, parsed.closeIdx + 1));
      from = parsed.closeIdx;
    }
  }
  from = 0;
  while (true) {
    const re = /\b(\w+)\.\$delete\(/g;
    re.lastIndex = from;
    const m = re.exec(out);
    if (!m) break;
    const idx = m.index;
    const callLen = m[0].length;
    if (inLineComment(idx)) {
      from = idx + callLen;
      continue;
    }
    const parsed = parseCallArgs(out, idx + callLen - 1);
    if (!parsed) break;
    if (parsed.args.length === 2) {
      const rep = `delete ${parsed.args[0]}[${parsed.args[1]}]`;
      out = out.slice(0, idx) + rep + out.slice(parsed.closeIdx + 1);
      from = idx + rep.length;
    } else {
      issue('delete-args', file, out.slice(idx, parsed.closeIdx + 1));
      from = parsed.closeIdx;
    }
  }
  return out;
}

function transformLifecycle(code) {
  return code.replace(/\bbeforeDestroy\s*\(/g, 'beforeUnmount(').replace(/\bdestroyed\s*\(/g, 'unmounted(');
}

function transformEnv(code) {
  return code.replace(/process\.env\.VUE_APP_/g, 'import.meta.env.VITE_APP_');
}

function transformElementUiImport(code, file) {
  let out = code;
  if (/import\s+'element-ui\/lib\/theme-chalk\/index\.css'/.test(out)) {
    out = out.replace(/import\s+'element-ui\/lib\/theme-chalk\/index\.css';?\n?/g, '');
    report.counts['el-css-removed'] = (report.counts['el-css-removed'] || 0) + 1;
  }
  const m = out.match(/import\s+\{([^}]+)\}\s+from\s+'element-ui';/);
  if (m) {
    const names = m[1]
      .split(',')
      .map((s) => s.trim())
      .filter(Boolean);
    const renamed = names.map((n) => EP_IMPORT_MAP[n] || n);
    out = out.replace(m[0], `import { ${renamed.join(', ')} } from 'element-plus';`);
    names.forEach((old, i) => {
      const neu = renamed[i];
      if (old !== neu) {
        out = out.replace(new RegExp(`\\b${old}\\b`, 'g'), neu);
      }
    });
    report.counts['el-import-fixed'] = (report.counts['el-import-fixed'] || 0) + 1;
  }
  out = out.replace(/from\s+'element-ui'/g, "from 'element-plus'");
  return out;
}

function insertImports(code, importLines) {
  if (!importLines.length) return code;
  const lines = code.split('\n');
  let insertAt = 0;
  // 跳过顶部版权注释块
  for (let i = 0; i < lines.length; i++) {
    const t = lines[i].trim();
    if (t === '' || t.startsWith('//') || t.startsWith('/*') || t.startsWith('*') || t.startsWith('*/')) {
      insertAt = i + 1;
      if (t === '' && i > 2) break;
    } else break;
  }
  lines.splice(insertAt, 0, ...importLines, '');
  return lines.join('\n');
}

function transformRequireAssets(code, file, isVue) {
  const imports = [];
  const map = new Map();
  let counter = 0;
  let out = code.replace(/require\((['"])([^'"]+)\1\)/g, (full, q, p) => {
    if (!ASSET_EXT.test(p) || p.includes('${')) return full;
    if (!map.has(p)) {
      const base = path
        .basename(p)
        .replace(/\.[^.]+$/, '')
        .replace(/[^\w]/g, '_');
      const id = `__asset_${base}_${counter++}`;
      map.set(p, id);
      imports.push(`import ${id} from '${p}';`);
    }
    report.counts['require->import'] = (report.counts['require->import'] || 0) + 1;
    return map.get(p);
  });
  if (!imports.length) return out;
  return insertImports(out, imports);
}

// ---------------- SFC 处理 ----------------
function transformVueFile(code, file) {
  let out = code;
  // template 块（贪婪匹配第一个 <template> 到最后一个 </template>）
  const tplStart = out.search(/<template[^>]*>/);
  if (tplStart !== -1) {
    const tplEnd = out.lastIndexOf('</template>');
    if (tplEnd > tplStart) {
      const inner = out.slice(tplStart, tplEnd + '</template>'.length);
      let newTpl = inner;
      newTpl = transformSlotSyntax(newTpl, file);
      newTpl = transformSync(newTpl);
      newTpl = transformNative(newTpl);
      newTpl = transformFiltersInTpl(newTpl, file);
      newTpl = transformTplAttrHelpers(newTpl, file);
      const tplRequire = newTpl.match(/require\((['"])[^'"]+\1\)/g);
      if (tplRequire) issue('tpl-require', file, `${tplRequire.length} 处（转 script setup 时改为顶部 import）`);
      out = out.slice(0, tplStart) + newTpl + out.slice(tplEnd + '</template>'.length);
    }
  }
  // script 块
  const scriptRe = /<script([^>]*)>([\s\S]*?)<\/script>/g;
  out = out.replace(scriptRe, (full, attrs, body) => {
    let nb = body;
    nb = transformSetDelete(nb, file);
    nb = transformLifecycle(nb);
    nb = transformEnv(nb);
    nb = transformElementUiImport(nb, file);
    nb = transformRequireAssets(nb, file, true);
    if (/import\s+Vue\s+from\s+'vue'/.test(nb)) issue('import-vue-default', file, '');
    return `<script${attrs}>${nb}</script>`;
  });
  // style 块
  const styleRe = /<style([^>]*)>([\s\S]*?)<\/style>/g;
  out = out.replace(styleRe, (full, attrs, body) => `<style${attrs}>${transformStyle(body, file)}</style>`);
  return out;
}

function transformJsFile(code, file) {
  let out = code;
  out = transformSetDelete(out, file);
  out = transformLifecycle(out);
  out = transformEnv(out);
  out = transformElementUiImport(out, file);
  out = transformRequireAssets(out, file, false);
  if (/import\s+Vue\s+from\s+'vue'/.test(out)) issue('import-vue-default', file, '');
  return out;
}

// ---------------- 遍历执行 ----------------
function walk(dir, cb) {
  for (const name of fs.readdirSync(dir)) {
    const p = path.join(dir, name);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p, cb);
    else cb(p);
  }
}

walk(SRC, (p) => {
  if (!/\.(vue|js)$/.test(p)) return;
  const rel = path.relative(ROOT, p);
  const code = fs.readFileSync(p, 'utf8');
  report.files++;
  const out = p.endsWith('.vue') ? transformVueFile(code, rel) : transformJsFile(code, rel);
  if (out !== code) {
    report.changed++;
    if (!DRY) fs.writeFileSync(p, out);
  }
});

fs.writeFileSync(path.join(ROOT, 'scripts/codemods/report.json'), JSON.stringify(report, null, 2));
const summary = Object.entries(report.counts)
  .map(([k, v]) => `${k}: ${v}`)
  .join(', ');
console.log(`扫描 ${report.files} 个文件，改动 ${report.changed} 个${DRY ? '（dry 模式未写入）' : ''}`);
console.log(`转换统计: ${summary || '无'}`);
console.log(`待人工处理 issue: ${report.issues.length} 条，明细见 scripts/codemods/report.json`);
const byType = {};
report.issues.forEach((i) => (byType[i.type] = (byType[i.type] || 0) + 1));
console.log('issue 分布:', JSON.stringify(byType));
