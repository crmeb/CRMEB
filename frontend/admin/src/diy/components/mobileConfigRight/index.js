import { markRaw } from 'vue';

const modules = import.meta.glob('./*.vue', { eager: true });

const obj = {};
Object.keys(modules).forEach((key) => {
  // markRaw：配置面板将控件组件存入 rCom 等 ref 数组，避免被深度响应式代理
  // 触发 "Vue received a Component that was made a reactive object" 警告
  obj[key.replace(/(\.\/|\.vue)/g, '')] = markRaw(modules[key].default);
});
export default obj;
