import { defineAsyncComponent } from 'vue';

/**
 * vxe-table / vxe-pc-ui 按需装载。
 * 禁止在 main.js 同步 import（约 1.8MB JS+CSS 会进首屏）。
 *
 * 关键：SFC 会在 setup 顶部 resolveComponent('vxe-table')，若此时尚未 app.use，
 * 会得到失败占位，即使随后注册成功表格也不会渲染。因此 mount 前先注册异步壳组件。
 */

let installing;

/**
 * @param {import('vue').App} app
 * @returns {Promise<void>}
 */
export function setupVxe(app) {
  if (app.config.globalProperties.$vxeReady) {
    return Promise.resolve();
  }
  if (!installing) {
    installing = Promise.all([import('vxe-table'), import('vxe-pc-ui')])
      .then(async ([VxeTableMod, VxeUIMod]) => {
        await Promise.all([import('vxe-table/lib/style.css'), import('vxe-pc-ui/lib/style.css')]);
        app.use(VxeUIMod.default);
        app.use(VxeTableMod.default);
        app.config.globalProperties.$vxeReady = true;
      })
      .catch((err) => {
        installing = undefined;
        throw err;
      });
  }
  return installing;
}

/** 模板里实际用到的 vxe 组件（PascalCase，对应 resolveComponent 查找名） */
const VXE_PLACEHOLDER_NAMES = [
  'VxeTable',
  'VxeColumn',
  'VxeTableColumn',
  'VxeInput',
  'VxeSelect',
  'VxeOption',
  'VxeButton',
];

/**
 * createApp 之后、mount 之前调用：注册异步壳，保证页面 resolveComponent 能命中。
 * @param {import('vue').App} app
 */
export function registerVxePlaceholders(app) {
  VXE_PLACEHOLDER_NAMES.forEach((name) => {
    const placeholder = defineAsyncComponent(async () => {
      await setupVxe(app);
      const comp = app._context.components[name];
      if (!comp || comp === placeholder) {
        throw new Error(`[vxe] 装载后仍缺少组件 ${name}`);
      }
      return comp;
    });
    app.component(name, placeholder);
  });
}
