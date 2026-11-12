import { markRaw } from 'vue';

const modules = import.meta.glob('./*.vue', { eager: true });

const obj = {};
Object.keys(modules).forEach((key) => {
  const comp = markRaw(modules[key].default);
  // 组件在独立 <script> 块具名导出的默认配置，挂为组件静态属性；
  // DIY 编辑器新增组件时 store 经此取默认配置（原 Vue2 data().defaultConfig）
  if (modules[key].getDefaultConfig) {
    comp.getDefaultConfig = modules[key].getDefaultConfig;
  }
  if (modules[key].defaultConfig) {
    comp.defaultConfig = modules[key].defaultConfig;
  }
  obj[key.replace(/(\.\/|\.vue)/g, '')] = comp;
});

/**
 * DIY 预览组件依赖全局 <common_wrapper>，仅在进入装修页时注册，避免拖入入口包。
 * @param {import('vue').App} app
 */
export function registerDiyMobilePageGlobals(app) {
  if (app._context.components.common_wrapper) return;
  const wrapper = modules['./common_wrapper.vue']?.default;
  if (wrapper) app.component('common_wrapper', wrapper);
}

export default obj;
