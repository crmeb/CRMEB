// CRMEB 官方图标库（iconfont 项目 5121724）清单模块，资源文件见同目录 README.md。
// 图标清单从 crmeb_iconfont.json（官方下载包元数据）自动生成，官方图标更新时
// 覆盖同目录 crmeb_iconfont.* 文件即可，无需修改本模块。
import iconfontData from './crmeb_iconfont.json';

// 类名中的序号（如 CRMEB-950-jiantou-xianxing → 950），作为展示排序依据
function iconNumber(fontClass) {
  const matched = fontClass.match(/\d+/);
  return matched ? Number(matched[0]) : Number.MAX_SAFE_INTEGER;
}

// 中文名：官方 name 形如「CRMEB-950-箭头-线性」，去除项目前缀与线性/面性后缀
function iconLabel(name) {
  return name
    .replace(/\s+/g, '')
    .replace(/^(?:a-)?CRME[BD]-\d+-/, '')
    .replace(/-(线性|面性)$/, '');
}

// 图标清单：value 为字体类名，name 为中文名，num 为序号；按序号从小到大排序
export const crmebIcons = iconfontData.glyphs
  .map((glyph) => ({
    value: glyph.font_class,
    name: iconLabel(glyph.name),
    num: iconNumber(glyph.font_class),
  }))
  .sort((a, b) => a.num - b.num || a.value.localeCompare(b.value));

const iconSet = new Set(crmebIcons.map((item) => item.value));

// 解析官方图标类名（存量菜单等存储值可能非当前官方类名），无匹配返回空串：
// 1. 精确匹配；2. 唯一前缀匹配（兼容存储值被截断/缺样式后缀）；
// 3. 兼容旧版 a- 前缀类名（官方更新后已去掉 a- 前缀，如 a-CRMEB-921-chatgpt-mianxing）
export function resolveIconfontIcon(name) {
  if (!name) return '';
  if (iconSet.has(name)) return name;
  const prefixHits = crmebIcons.filter((item) => item.value.startsWith(name));
  if (prefixHits.length === 1) return prefixHits[0].value;
  const normalized = name.replace(/^a-/, '');
  if (iconSet.has(normalized)) return normalized;
  const suffixHits = crmebIcons.filter((item) => item.value.endsWith(normalized));
  return suffixHits.length === 1 ? suffixHits[0].value : '';
}

export default crmebIcons;
