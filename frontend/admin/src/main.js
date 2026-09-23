// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

// Vue 核心
import * as Vue from 'vue';
const { createApp } = Vue;
// pinia 必须最先装载：store/index.js 模块求值时即 setActivePinia，
// App/router 依赖链（如 setings.vue → i18n）中存在模块顶层 useXxxStore() 调用
import pinia from './store';
import App from './App';
import router from './router';
import { i18n } from '@/i18n/index.js';

// 配置和工具
import settings from '@/setting';
import * as tools from '@/libs/tools';
import timeOptions from '@/libs/timeOptions';

// UI 框架
import ElementPlus from 'element-plus';
import zhCn from 'element-plus/es/locale/lang/zh-cn';
import 'element-plus/dist/index.css';
import * as ElementPlusIconsVue from '@element-plus/icons-vue';
// element-ui 字体图标类名兼容层（CSS mask 方案，自动生成）
import './styles/el-icon-compat.css';

// 自定义组件和指令
import importDirective from '@/directive';
import installPlugin from '@/plugin';
import Pagination from '@/components/Pagination';
import pagesHeader from '@/components/pagesHeader';
import dateRangePicker from '@/components/dateRangePicker/index.vue';
import common_wrapper from '@diy/components/mobilePage/common_wrapper.vue';
import iconCompat from '@/components/iconCompat/index.vue';
import imgModal from './components/uploadPictures/model';
import videoModal from './components/uploadVideo2/model';
import lazyDirective from '@/utils/lazyDirective';

// 第三方库
import VxeTable from 'vxe-table';
import VxeUIAll from 'vxe-pc-ui';
import Viewer from 'v-viewer';
import formCreate from '@form-create/element-ui';

// 工具函数
import bus from '@/utils/bus';
import modalForm from '@/utils/modalForm';
import videoCloud from '@/utils/videoCloud';
import { modalSure, HandlePrice } from '@/utils/public';

// 样式文件
import './assets/fonts/font.css';
import '@/theme/index.scss';
import './assets/iconfontYI/iconfontYI.css';
import './plugin/emoji-awesome/css/google.min.css';
import './styles/index.scss';
import './styles/font/iconfont.js';
import 'viewerjs/dist/viewer.css';
import 'vxe-table/lib/style.css';
import 'vxe-pc-ui/lib/style.css';

// 全局过滤器（Vue3 以 $filters 形式挂载）
import * as filters from './filters';

const app = createApp(App);

// 注册全局变量，供 UMD 扩展模块运行时使用（externals: vue -> window.Vue, element-plus -> window.ElementPlus）
window.Vue = Vue;
window.ElementPlus = ElementPlus;

// ---- 过渡兼容层：Vue2 时代的 this.bus（$on/$off/$emit 转发到 mitt 总线） ----
app.config.globalProperties.bus = {
  $on: (type, fn) => bus.on(type, fn),
  $off: (type, fn) => bus.off(type, fn),
  $once: (type, fn) => {
    const wrap = (e) => {
      bus.off(type, wrap);
      fn(e);
    };
    bus.on(type, wrap);
  },
  $emit: (type, e) => bus.emit(type, e),
};

// 注册全局组件
app.component('Pagination', Pagination);
app.component('pagesHeader', pagesHeader);
app.component('common_wrapper', common_wrapper);
// 日期范围搜索（内置系统快捷项，见 libs/timeOptions）
app.component('dateRangePicker', dateRangePicker);
// iView <Icon type="..."> 兼容（面包屑/菜单/DIY 等历史模板仍在使用）
app.component('Icon', iconCompat);

// Element Plus 图标组件全量注册（新写法 <el-icon><Edit /></el-icon> 可直接使用）
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component);
}

// 注册插件
app.use(pinia);
app.use(router);
app.use(i18n);
app.use(ElementPlus, { locale: zhCn });
app.use(formCreate);
app.use(VxeUIAll);
app.use(VxeTable);
app.use(imgModal);
app.use(videoModal);
app.use(Viewer, {
  defaultOptions: {
    zIndex: 9999,
  },
});

/**
 * @description 注册admin内置插件
 */
installPlugin(app);

/**
 * @description 图片懒加载指令（替代 vue-lazyload）
 */
app.directive('lazy', lazyDirective);

/**
 * @description 注册应用配置
 */
app.config.globalProperties.$routeProStr = settings.routePre;
app.config.globalProperties.$modalForm = modalForm;
app.config.globalProperties.$modalSure = modalSure;
app.config.globalProperties.$HandlePrice = HandlePrice;
app.config.globalProperties.$videoCloud = videoCloud;
app.config.globalProperties.$timeOptions = timeOptions;
app.config.globalProperties.$tools = tools;
app.config.globalProperties.$filters = filters;
app.config.globalProperties.$copyText = (text) => {
  return new Promise((resolve, reject) => {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(text)
        .then(resolve)
        .catch(() => reject({ msg: '复制失败' }));
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand('copy') ? resolve() : reject({ msg: '复制失败' });
      } catch (e) {
        reject({ msg: '复制失败' });
      }
      document.body.removeChild(textarea);
    }
  });
};

/**
 * 注册指令
 */
importDirective(app);

// 添加统计脚本：统计异常不能影响后台主流程
function loadStatisticsScript(src) {
  const appendScript = () => {
    try {
      const script = document.createElement('script');
      script.async = true;
      script.defer = true;
      script.src = src;
      script.onerror = () => {
        script.onerror = null;
        if (script.parentNode) script.parentNode.removeChild(script);
      };
      (document.body || document.head || document.documentElement).appendChild(script);
    } catch (e) {
      // ignore
    }
  };

  if (window.requestIdleCallback) {
    window.requestIdleCallback(appendScript, { timeout: 3000 });
  } else {
    window.setTimeout(appendScript, 0);
  }
}

// 添加crmeb chat 统计
fetch(`${settings.apiBaseURL}/custom_admin_js`)
  .then((response) => response.text())
  .then((content) => {
    // 尝试解析是否为HTML（带<script>标签）
    const isHTML = content.trim().startsWith('<script');

    let externalScripts = [];
    let inlineScripts = [];

    if (isHTML) {
      // 情况1：带<script>标签，用DOMParser解析
      const parser = new DOMParser();
      const doc = parser.parseFromString(content, 'text/html');
      const scripts = doc.querySelectorAll('script');

      externalScripts = Array.from(scripts).filter((script) => script.src);
      inlineScripts = Array.from(scripts).filter((script) => !script.src);
    } else {
      // 情况2：不带<script>标签，直接当作内联脚本处理
      inlineScripts = [
        {
          textContent: content,
        },
      ];
    }

    // 1. 先加载所有外部脚本（如果有）
    const loadExternalScripts = externalScripts.map((script) => {
      return new Promise((resolve, reject) => {
        const newScript = document.createElement('script');
        newScript.src = script.src;
        newScript.onload = resolve;
        newScript.onerror = reject;
        document.body.appendChild(newScript);
      });
    });

    // 2. 等外部脚本加载完成后，再执行内联脚本
    Promise.all(loadExternalScripts)
      .then(() => {
        inlineScripts.forEach((script) => {
          const newScript = document.createElement('script');
          newScript.textContent = script.textContent;
          document.body.appendChild(newScript);
        });
      })
      .catch((error) => console.error('Failed to load external scripts:', error));
  })
  .catch((error) => console.error('Error fetching script:', error));

app.mount('#app');

loadStatisticsScript('https://cdn.oss.9gt.net/js/es.js?version=bzv6.0.0');
