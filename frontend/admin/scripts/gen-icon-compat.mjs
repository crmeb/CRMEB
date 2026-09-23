// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 生成 src/styles/el-icon-compat.css：
// 把项目中仍在使用的 element-ui 字体图标类名（el-icon-*）映射为
// @element-plus/icons-vue 的 SVG（CSS mask 方案，颜色跟随 currentColor），
// 使 <i class="el-icon-edit"> 与 icon="el-icon-edit" 字符串用法无需改模板即可继续显示。
// 用法：node scripts/gen-icon-compat.mjs
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, URL } from 'node:url';

const ROOT = fileURLToPath(new URL('..', import.meta.url));
const ICONS_DIST = path.join(ROOT, 'node_modules/@element-plus/icons-vue/dist/index.js');

// element-ui 字体图标名 -> element-plus 图标名（kebab）的人工映射（未列出的先做同名匹配）
const OVERRIDES = {
  'el-icon-s-home': 'home-filled',
  'el-icon-s-platform': 'monitor',
  'el-icon-s-shop': 'shopping-cart',
  'el-icon-s-market': 'bell',
  'el-icon-money': 'money',
  'el-icon-s-goods': 'goods',
  'el-icon-s-order': 'list',
  'el-icon-s-data': 'data-line',
  'el-icon-s-marketing': 'data-analysis',
  'el-icon-s-custom': 'user',
  'el-icon-s-claim': 'circle-check',
  'el-icon-s-finance': 'money',
  'el-icon-s-management': 'files',
  'el-icon-s-promotion': 'promotion',
  'el-icon-s-tools': 'tools',
  'el-icon-s-help': 'help-filled',
  'el-icon-s-unfold': 'expand',
  'el-icon-s-fold': 'fold',
  'el-icon-s-grid': 'grid',
  'el-icon-s-comment': 'chat-dot-round',
  'el-icon-s-cooperation': 'connection',
  'el-icon-s-claim1': 'circle-check',
  'el-icon-s-operation': 'operation',
  'el-icon-s-open': 'open',
  'el-icon-s-check': 'circle-check',
  'el-icon-s-opportunity': 'opportunity',
  'el-icon-s-release': 'release',
  'el-icon-s-ticket': 'ticket',
  'el-icon-s-flag': 'flag',
  'el-icon-s-claim': 'circle-check',
  'el-icon-warning': 'warning',
  'el-icon-warning-outline': 'warning',
  'el-icon-question': 'question-filled',
  'el-icon-info': 'info-filled',
  'el-icon-remove': 'circle-close',
  'el-icon-success': 'circle-check',
  'el-icon-error': 'circle-close-filled',
  'el-icon-share': 'share',
  'el-icon-star-off': 'star',
  'el-icon-star-on': 'star-filled',
  'el-icon-phone-outline': 'phone',
  'el-icon-edit-outline': 'edit-pen',
  'el-icon-delete-solid': 'delete-filled',
  'el-icon-user-solid': 'user-filled',
  'el-icon-message-solid': 'message',
  'el-icon-bell': 'bell',
  'el-icon-s-platform1': 'monitor',
  'el-icon-mobile-phone': 'iphone',
  'el-icon-loading': 'loading',
  'el-icon-view': 'view',
  'el-icon-c-scale-to-original': 'scale-to-original',
  'el-icon-check-circle': 'circle-check',
  'el-icon-time': 'clock',
  'el-icon-upload2': 'upload',
  'el-icon-yuan': 'coin',
  'el-icon-zoom-in': 'zoom-in',
  'el-icon-zoom-out': 'zoom-out',
  'el-icon-rank': 'rank',
  'el-icon-refresh-left': 'refresh-left',
  'el-icon-refresh-right': 'refresh-right',
  'el-icon-d-arrow-left': 'd-arrow-left',
  'el-icon-d-arrow-right': 'd-arrow-right',
  'el-icon-video-camera-solid': 'video-camera-filled',
  'el-icon-camera-solid': 'camera-filled',
  'el-icon-s-comment1': 'chat-dot-round',
  'el-icon-fa': 'more',
};

const camelize = (s) => s.replace(/-(\w)/g, (_, c) => c.toUpperCase());
const kebab = (s) => s.replace(/[A-Z]/g, (c) => '-' + c.toLowerCase()).replace(/^-/, '');

// 1. 收集 dist/index.js 里每个图标的 path d 集合
const dist = fs.readFileSync(ICONS_DIST, 'utf8');
const iconPaths = {}; // kebab -> [d, ...]
const blocks = dist.split(/\/\/ src\/components\//).slice(1);
for (const b of blocks) {
  const nameM = b.match(/name: "([A-Za-z0-9-]+)"/);
  if (!nameM) continue;
  const k = kebab(nameM[1]);
  const ds = [...b.matchAll(/d: "([^"]+)"/g)].map((m) => m[1]);
  if (ds.length) iconPaths[k] = ds;
}

// 2. 扫描 src 中使用的 el-icon-* 类名
const used = new Set();
const walk = (dir) => {
  for (const n of fs.readdirSync(dir)) {
    const p = path.join(dir, n);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p);
    else if (/\.(vue|js)$/.test(n)) {
      const c = fs.readFileSync(p, 'utf8');
      for (const m of c.matchAll(/el-icon-[a-z0-9]+[a-z0-9-]*/g)) {
        if (!m[0].includes('--')) used.add(m[0]);
      }
    }
  }
};
walk(path.join(ROOT, 'src'));

// 3. 映射并生成 css
const svgUrl = (ds) =>
  `url("data:image/svg+xml,${encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">${ds
      .map((d) => `<path fill="black" d="${d}"/>`)
      .join('')}</svg>`,
  )}")`;

const lines = [
  '/* eslint-disable */',
  '/* 本文件由 scripts/gen-icon-compat.mjs 自动生成，请勿手改；新增图标后重新生成。',
  '   作用：element-ui 字体图标类名 -> Element Plus SVG 图标（CSS mask，颜色随 currentColor），',
  '   兼容历史 <i class="el-icon-*"> 与 icon="el-icon-*" 字符串用法。 */',
];
const unmapped = [];
// OVERRIDES 全量纳入：部分旧类名（如菜单管理存库的 el-icon-s-home）不在源码中出现，扫描不到
for (const cls of [...new Set([...used, ...Object.keys(OVERRIDES)])].sort()) {
  const short = cls.replace(/^el-icon-/, '');
  let target = OVERRIDES[cls] !== undefined ? OVERRIDES[cls] : iconPaths[short] ? short : undefined;
  if (!iconPaths[target]) {
    // 尝试加 -filled / 模糊匹配
    for (const cand of [short + '-filled', short.replace(/-outline$/, ''), camelize(short).toLowerCase()]) {
      if (iconPaths[cand]) {
        target = cand;
        break;
      }
    }
  }
  if (!iconPaths[target]) {
    unmapped.push(cls);
    continue;
  }
  lines.push(`.${cls} {
  display: inline-block;
  width: 1em;
  height: 1em;
  vertical-align: -0.125em;
  background-color: currentColor;
  -webkit-mask: ${svgUrl(iconPaths[target])} no-repeat center / contain;
  mask: ${svgUrl(iconPaths[target])} no-repeat center / contain;
}`);
}
lines.push(`.el-icon-loading {
  animation: el-icon-compat-rotating 2s linear infinite;
}
@keyframes el-icon-compat-rotating {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(360deg);
  }
}`);
fs.writeFileSync(path.join(ROOT, 'src/styles/el-icon-compat.css'), lines.join('\n') + '\n');
console.log(`使用中的图标类名: ${used.size} 个，已映射 ${used.size - unmapped.length} 个，未映射 ${unmapped.length} 个`);
if (unmapped.length) console.log('未映射:', unmapped.join(', '));
