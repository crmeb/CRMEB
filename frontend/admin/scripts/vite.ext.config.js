// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 扩展 UMD 独立构建配置（由 scripts/build-extensions.js 通过环境变量驱动）：
//   EXT_ENTRY 入口文件绝对路径 / EXT_NAME UMD 全局名 / EXT_OUT 输出目录
// vue 与 element-plus 走 externals，运行时从宿主 window.Vue / window.ElementPlus 获取。
import path from 'node:path';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

const resolve = (dir) => path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', dir);

export default defineConfig({
  plugins: [vue()],
  resolve: {
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    alias: [
      { find: '@', replacement: resolve('src') },
      { find: '_c', replacement: resolve('src/components') },
      { find: '@api', replacement: resolve('src/api') },
      { find: '@diy', replacement: resolve('src/diy') },
      // vue-tree-list@2 的 exports 映射指向不存在的 dist/style.css（上游打包 bug），
      // alias 直连真实 css 文件绕过（uploadPictures 等组件会被打进扩展 UMD）
      {
        find: /^vue-tree-list\/dist\/vue-tree-list\.css$/,
        replacement: resolve('node_modules/vue-tree-list/dist/vue-tree-list.css'),
      },
    ],
  },
  css: {
    preprocessorOptions: {
      scss: {
        silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'slash-div'],
      },
    },
  },
  build: {
    outDir: process.env.EXT_OUT,
    emptyOutDir: true,
    cssCodeSplit: false,
    sourcemap: false,
    lib: {
      entry: process.env.EXT_ENTRY,
      name: process.env.EXT_NAME,
      formats: ['umd'],
      fileName: () => 'app.umd.js',
      // Vite 6+ lib CSS 默认跟包名；扩展产物约定仍为 app.umd.css
      cssFileName: 'app.umd',
    },
    rolldownOptions: {
      external: ['vue', 'element-plus'],
      output: {
        // 入口同时有 default 与 named export（routes），避免 MIXED_EXPORTS 警告
        exports: 'named',
        globals: { vue: 'Vue', 'element-plus': 'ElementPlus' },
        assetFileNames: 'app.umd[extname]',
      },
    },
  },
});
