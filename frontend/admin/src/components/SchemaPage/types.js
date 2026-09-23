/**
 * 通用页面类型字典（封闭集）
 *
 * 与后端 SchemaConfigValidator 白名单保持一致；未知类型由后端降级，前端无需兜底。
 */

/** 页面类型 */
export const PAGE_TYPES = {
  LIST: 'list',
  FORM: 'form',
  STAT: 'stat',
  DETAIL: 'detail',
};

/** 表格列类型 */
export const COLUMN_TYPES = [
  'text',
  'number',
  'amount',
  'datetime',
  'date',
  'tag',
  'image',
  'switch',
  'link',
  'ellipsis',
];

/** 搜索字段类型 */
export const SEARCH_TYPES = ['input', 'number', 'select', 'date', 'daterange'];

/** 行操作模式 */
export const ACTION_MODES = {
  DRAWER_FORM: 'drawer_form', // 右侧抽屉打开表单（form-create）
  DRAWER_DETAIL: 'drawer_detail', // 右侧抽屉打开详情
  CONFIRM_ACTION: 'confirm_action', // 确认弹窗后执行动作
  DIRECT_ACTION: 'direct_action', // 直接执行动作
  NAVIGATE: 'navigate', // 页面跳转
};

/**
 * @description 格式化时间（后端 time() 整型秒 → 文本）
 * @param {number|string} value 秒级时间戳
 * @param {boolean} withTime 是否带时分秒
 * @returns {string}
 */
export function formatTime(value, withTime = true) {
  if (!value) return '-';
  const num = Number(value);
  if (!Number.isFinite(num) || num <= 0) return String(value);
  const d = new Date(num * 1000);
  const pad = (n) => String(n).padStart(2, '0');
  const date = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  if (!withTime) return date;
  return `${date} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

/**
 * @description 千分位数值格式化
 * @param {number|string} value
 * @returns {string}
 */
export function formatNumber(value) {
  const num = Number(value);
  if (!Number.isFinite(num)) return String(value ?? '-');
  return num.toLocaleString('zh-CN');
}
