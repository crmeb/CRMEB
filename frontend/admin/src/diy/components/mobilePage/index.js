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
export default obj;
