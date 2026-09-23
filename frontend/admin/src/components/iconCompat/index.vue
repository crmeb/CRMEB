<template>
  <component v-if="epName" :is="epName" class="iview-icon-compat" :style="styleObj" />
  <i v-else-if="compatClass" :class="compatClass" class="iview-icon-compat" :style="styleObj" />
  <i v-else class="iview-icon-compat iview-icon-compat--empty" :style="styleObj" />
</template>
<script setup>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * iView Icon 兼容组件
 * @description Vue2/iView 时代的全局 Icon 图标组件迁移后未注册，图标静默丢失。
 * 按 type 值的四种来源做兼容渲染：
 *  1. iView ionicon（ios-xx / md-xx / logo-xx 前缀，硬编码在少量模板里）→ 内置映射到 Element Plus 图标
 *  2. Element Plus 组件名（菜单数据 el-icon-Crown / Crown，含后端截断如 el-icon-Cellphon）→ 动态解析已全局注册的 EP 图标
 *  3. CRMEB 官方 iconfont 类名（菜单存量数据如 CRMEB-131-shangpin-mianxing）→ crmeb-icon 字体类
 *  4. element-ui 字体类名（s-order / user-solid 等）→ 复用 styles/el-icon-compat.css 的 mask 方案
 */
import { computed } from 'vue';
import * as EpIcons from '@element-plus/icons-vue';
import { resolveIconfontIcon } from '@/styles/font/crmeb/icons';

defineOptions({ name: 'Icon' });

const props = defineProps({
  type: { type: String, default: '' },
  size: { type: [Number, String], default: '' },
  color: { type: String, default: '' },
});

// iView ionicon → Element Plus 图标名（仅覆盖项目中实际出现的）
const IVIEW_MAP = {
  'ios-folder': 'Folder',
  'logo-linkedin': 'Connection',
  'ios-keypad-outline': 'Grid',
  'ios-camera-outline': 'CameraFilled',
  'ios-refresh': 'Refresh',
  'ios-close': 'Close',
  'md-radio-button-on': 'CircleCheckFilled',
  'md-radio-button-off': 'CircleFilled',
  'md-menu': 'Menu',
  'ios-people': 'User',
  'ios-gear': 'Setting',
  'md-home': 'HomeFilled',
  'ios-home': 'HomeFilled',
};

const epNames = Object.keys(EpIcons);

function resolveEp(name) {
  if (!name) return '';
  if (epNames.includes(name)) return name;
  // 后端菜单 icon 存在截断（如 Cellphon / Connecti / GoldMeda），前缀匹配补全
  if (name.length >= 4) {
    const hit = epNames.find((n) => n.startsWith(name));
    if (hit) return hit;
  }
  return '';
}

const parsed = computed(() => {
  const t = (props.type || '').trim();
  if (!t) return { epName: '', compatClass: '' };

  if (/^(ios|md|logo)-/.test(t)) return { epName: IVIEW_MAP[t] || '', compatClass: '' };

  const bare = t.replace(/^el-icon-/, '');
  if (!bare) return { epName: '', compatClass: '' };

  // Element Plus 组件名风格（大驼峰，如 Crown / Goods）
  const pascal = bare[0].toUpperCase() + bare.slice(1);
  const epName = resolveEp(pascal);
  if (epName) return { epName, compatClass: '' };

  // CRMEB 官方 iconfont 类名（菜单存量数据，如 CRMEB-131-shangpin-mianxing），与 menuIcon 解析一致
  const iconfont = resolveIconfontIcon(t);
  if (iconfont) return { epName: '', compatClass: `crmeb-icon ${iconfont}` };

  // element-ui 字体类名风格（小写 / s- 前缀），复用 el-icon-compat.css
  return { epName: '', compatClass: `el-icon-${bare.toLowerCase()}` };
});

const epName = computed(() => parsed.value.epName);
const compatClass = computed(() => parsed.value.compatClass);

const styleObj = computed(() => {
  const s = {};
  if (props.size) s.fontSize = typeof props.size === 'number' ? `${props.size}px` : props.size;
  if (props.color) s.color = props.color;
  return s;
});
</script>
<style scoped>
.iview-icon-compat {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  vertical-align: middle;
  line-height: 1;
}
</style>
