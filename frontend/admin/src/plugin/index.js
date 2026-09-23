import config from '@/config';
import errorStore from './error-store';

const { plugin } = config;

// admin 内置插件注册表（替代 webpack 动态 require）
const pluginModules = {
  'error-store': errorStore,
};

export default (app) => {
  for (let name in plugin) {
    const value = plugin[name];
    const module = pluginModules[name];
    if (module) app.use(module, typeof value === 'object' ? value : undefined);
  }
};
