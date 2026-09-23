/**
 * 开发模式扩展加载器（仅 dev 构建使用）
 *
 * 通过 import.meta.glob 惰性加载扩展源码（src/pages/plugin/{key}/index.umd.js），
 * 支持 HMR 热更新，无需执行 build:ext。
 *
 * 生产构建：vite.config.js 的 crmeb-dev-ext-loader-stub 插件会把本模块替换为 stub，
 * 扩展源码不会进入生产产物
 * （生产环境由 PageContainer 运行时从 /admin/static/extensions/{key}/ 加载 UMD）。
 */
const loaders = import.meta.glob('../../plugin/*/index.umd.js');

export function loadDevExtension(typeKey) {
  const loader = loaders[`../../plugin/${typeKey}/index.umd.js`];
  if (!loader) {
    return Promise.reject(new Error(`未找到扩展 ${typeKey} 的入口 index.umd.js`));
  }
  return loader();
}
