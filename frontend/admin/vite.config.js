// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import AutoImport from 'unplugin-auto-import/vite';
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers';
import monacoEditorPluginModule from 'vite-plugin-monaco-editor';
const monacoEditorPlugin = monacoEditorPluginModule.default || monacoEditorPluginModule;

const resolve = (dir) => fileURLToPath(new URL(dir, import.meta.url));

// 开发期静态服务扩展 UMD 产物（对齐原 vue.config.js devServer.before：
// 将 dist/static/extensions 挂载到 /admin/static/extensions，供 PageContainer 动态加载）
function extensionsStaticPlugin() {
  const root = resolve('dist/static/extensions');
  const MIME = {
    '.js': 'text/javascript; charset=utf-8',
    '.mjs': 'text/javascript; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
    '.map': 'application/json; charset=utf-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.woff': 'font/woff',
    '.woff2': 'font/woff2',
    '.ttf': 'font/ttf',
  };
  return {
    name: 'crmeb-extensions-static',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        const url = new URL(req.url, 'http://localhost');
        if (!url.pathname.startsWith('/admin/static/extensions/')) return next();
        const rel = decodeURIComponent(url.pathname.replace('/admin/static/extensions/', '')).replace(/^\/+/, '');
        const file = path.join(root, rel);
        if (!file.startsWith(root) || !fs.existsSync(file) || !fs.statSync(file).isFile()) return next();
        res.setHeader('Content-Type', MIME[path.extname(file).toLowerCase()] || 'application/octet-stream');
        fs.createReadStream(file).pipe(res);
      });
    },
  };
}

// 生产构建剔除扩展开发加载器（对齐原 webpack IgnorePlugin(/dev-extension-loader$/)）：
// build 时把 './dev-extension-loader' 解析为 stub，扩展源码不进主包；
// dev 时走真实文件（内部 import.meta.glob 惰性加载 + HMR）。
function devExtLoaderStubPlugin(command) {
  const STUB_ID = '\0dev-extension-loader-stub';
  return {
    name: 'crmeb-dev-ext-loader-stub',
    enforce: 'pre',
    resolveId(source) {
      if (command === 'build' && /dev-extension-loader$/.test(source)) return STUB_ID;
      return null;
    },
    load(id) {
      if (id === STUB_ID) {
        return 'export function loadDevExtension(typeKey) { return Promise.reject(new Error("扩展开发加载器仅在 dev 模式可用")); }';
      }
      return null;
    },
  };
}

export default defineConfig(({ command }) => ({
  // 与 publicPath / routePre 一致：非根路径部署
  base: '/admin',
  plugins: [
    vue(),
    // script setup 下自动引入 vue API（ref/computed/onMounted 等），与显式 import 共存；
    // ElementPlusResolver 负责 script 侧 ElMessage/ElMessageBox 自动引入
    AutoImport({
      imports: ['vue'],
      resolvers: [ElementPlusResolver()],
      dts: false,
    }),
    // el-* 组件由 main.js 全量 app.use(ElementPlus) 全局注册（UMD 扩展依赖 window.ElementPlus 全量对象，
    // 不能改按需加载），无需 unplugin-vue-components 重复解析模板
    extensionsStaticPlugin(),
    devExtLoaderStubPlugin(command),
    monacoEditorPlugin({
      // editor + json worker；语法高亮走 basic-languages 不需要独立 worker（与 src/libs/monaco.js 配套）
      languageWorkers: ['editorWorkerService', 'json'],
    }),
  ],
  resolve: {
    // 对齐原 webpack resolve.extensions（允许省略 .vue/.js 后缀的导入）
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    alias: [
      // 完整版运行时（含模板编译器），对齐原 runtimeCompiler: true
      { find: /^vue$/, replacement: resolve('node_modules/vue/dist/vue.esm-bundler.js') },
      // vue-tree-list 的 exports 映射指向不存在的 dist/style.css（上游打包 bug），alias 直连真实 css 文件绕过
      {
        find: /^vue-tree-list\/dist\/vue-tree-list\.css$/,
        replacement: resolve('node_modules/vue-tree-list/dist/vue-tree-list.css'),
      },
      { find: '@', replacement: resolve('src') },
      { find: '_c', replacement: resolve('src/components') },
      { find: '@api', replacement: resolve('src/api') },
      { find: '@diy', replacement: resolve('src/diy') },
    ],
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
        // mixed-decls 已在 sass 1.92+ 撤销弃用（混用声明合法化），再列入会触发 "obsolete" 元警告，勿加回
        silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions', 'slash-div'],
      },
    },
  },
  server: {
    port: 1619,
    host: true,
    proxy: {
      // 代理 API 请求到 PHP 后端（开发时需要启动后端服务）
      '/adminapi': {
        target: 'http://localhost:8011',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: 'dist',
    assetsDir: 'system_static',
    sourcemap: false,
    chunkSizeWarningLimit: 1500,
    rollupOptions: {
      output: {
        // 大依赖拆独立 chunk：长缓存友好，避免全部塞进入口（monaco/echarts/xlsx 本身只被懒加载页面引用）
        manualChunks(id) {
          if (!id.includes('node_modules')) return undefined;
          if (id.includes('monaco-editor')) return 'monaco-editor';
          if (id.includes('echarts') || id.includes('zrender')) return 'echarts';
          if (id.includes('element-plus') || id.includes('@element-plus')) return 'element-plus';
          if (id.includes('vxe-table') || id.includes('vxe-pc-ui') || id.includes('xe-utils')) return 'vxe';
          if (id.includes('@wangeditor')) return 'wangeditor';
          if (id.includes('@form-create')) return 'form-create';
          if (id.includes('xlsx')) return 'xlsx';
          return undefined;
        },
      },
    },
  },
  // 生产等价于原 UglifyJsPlugin drop_console/drop_debugger（保留 error/warn）
  esbuild: {
    drop: ['debugger'],
    pure: ['console.log'],
  },
}));
