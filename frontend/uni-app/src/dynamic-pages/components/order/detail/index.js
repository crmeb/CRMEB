/**
 * 订单详情 section 组件自动注册表
 * 通过 import.meta.glob 扫描当前目录所有 .vue 文件，按组件静态声明的 componentKey 注册
 * 新增组件只需在本目录放 .vue 文件并声明 componentKey，无需修改本文件或引擎
 */

// 扫描当前目录下所有 .vue 文件（不递归子目录），eager 立即导入
var modules = import.meta.glob('./*.vue', { eager: true });
var map = {};

Object.keys(modules).forEach(function (k) {
  var mod = modules[k];
  var comp = mod.default || mod;
  var ck = comp.componentKey;
  // 未声明 componentKey 的组件跳过（如纯工具组件）
  if (!ck) return;
  var keys = Array.isArray(ck) ? ck : [ck];
  keys.forEach(function (key) {
    map[key] = comp;
  });
});

/**
 * 按 section 解析命中的注册表 key
 * 查找顺序：复合 key（component:key）→ 单 key（component）→ null
 * 小程序端 SectionDispatcher 依赖本函数做分支判断（组件对象身份比较在
 * MP 编译后不可靠：import 被改写为加载函数，与注册表中的组件对象永不相等）
 * @param {object} section 区段对象，含 component / key
 * @return {string|null} 命中的注册表 key，未命中返回 null
 */
export function resolveSectionKey(section) {
  if (!section) return null;
  var composite = section.component + ':' + (section.key || '');
  if (map[composite]) return composite;
  if (map[section.component]) return section.component;
  return null;
}

/**
 * 按 section 解析对应组件对象（H5/APP 端动态组件用）
 * @param {object} section 区段对象，含 component / key
 * @return {object|null} 组件对象，未命中返回 null
 */
export function resolveSectionComponent(section) {
  var key = resolveSectionKey(section);
  return key ? map[key] : null;
}

export { default as InfoListComponent } from './InfoList.vue';
