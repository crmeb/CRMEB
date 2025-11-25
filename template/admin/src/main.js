// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

// Vue 核心
import Vue from 'vue';
import App from './App';
import router from './router';
import store from './store';
import { i18n } from '@/i18n/index.js';

// 配置和工具
import config from '@/config';
import settings from '@/setting';
import * as tools from '@/libs/tools';
import Auth from '@/libs/wechat';
import dialog from '@/libs/dialog';
import timeOptions from '@/libs/timeOptions';
import scroll from '@/libs/loading';

// UI 框架
import Element from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';

// 自定义组件和指令
import importDirective from '@/directive';
import { directive as clickOutside } from 'v-click-outside-x';
import installPlugin from '@/plugin';
import Pagination from '@/components/Pagination';
import pagesHeader from '@/components/pagesHeader';
import imgModal from './components/uploadPictures/model';
import videoModal from './components/uploadVideo2/model';

// 第三方库
import moment from 'moment';
import TreeTable from 'tree-table-vue';
import VOrgTree from 'v-org-tree';
import 'xe-utils';
import VxeTable from 'vxe-table';
import VxeUIAll from 'vxe-pc-ui';
import VueAwesomeSwiper from 'vue-awesome-swiper';
import VueLazyload from 'vue-lazyload';
import Viewer from 'v-viewer';
import VueDND from 'awe-dnd';
import formCreate from '@form-create/element-ui';
import VueCodeMirror from 'vue-codemirror';
import schema from 'async-validator';
import VueTreeList from 'vue-tree-list';
import vuescroll from 'vuescroll';
import VueClipboard from 'vue-clipboard2';

// 工具函数
import modalForm from '@/utils/modalForm';
import exportExcel from '@/utils/newToExcel.js';
import videoCloud from '@/utils/videoCloud';
import { modalSure, HandlePrice } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';

// 样式文件
import './assets/fonts/font.css';
import '@/assets/icons/iconfont.css';
import '@/assets/iconfont/iconfont.css';
import '@/assets/iconfont/iconfont.js';
import '@/theme/index.scss';
import './assets/iconfontYI/iconfontYI.css';
import './plugin/emoji-awesome/css/google.min.css';
import 'v-org-tree/dist/v-org-tree.css';
import './styles/index.scss';
import 'swiper/css/swiper.css';
import 'viewerjs/dist/viewer.css';
import 'codemirror/lib/codemirror.css';
import 'vxe-table/lib/style.css';
import 'vxe-table/lib/index.css';
import 'vxe-pc-ui/es/style.css';
import 'vue-happy-scroll/docs/happy-scroll.css';

// 全局过滤器
import * as filters from './filters';

// 全局事件总线
Vue.prototype.bus = new Vue();

// 注册全局组件
Vue.component('Pagination', Pagination);
Vue.component('pagesHeader', pagesHeader);

// 配置第三方库
moment.locale('zh-cn');
Vue.prototype.$moment = moment;

VueClipboard.config.copyText = true;

// 注册插件
Vue.use(Element, { i18n: (key, value) => i18n.t(key, value), size: 'small' });
Vue.use(formCreate);
Vue.use(VueCodeMirror);
Vue.use(VueDND);
Vue.use(TreeTable);
Vue.use(VOrgTree);
Vue.use(VueAwesomeSwiper);
Vue.use(VxeUIAll);
Vue.use(VxeTable);
Vue.use(vuescroll);
Vue.use(imgModal);
Vue.use(videoModal);
Vue.use(VueClipboard);
Vue.use(VueTreeList);

// 配置懒加载
Vue.use(VueLazyload, {
  preLoad: 1.3,
  error: require('./assets/images/no.png'),
  loading: require('./assets/images/moren.jpg'),
  attempt: 1,
  listenEvents: ['scroll', 'wheel', 'mousewheel', 'resize', 'animationend', 'transitionend', 'touchmove'],
});

// 配置图片查看器
Vue.use(Viewer, {
  defaultOptions: {
    zIndex: 9999,
  },
});

// 自定义 Element Message
const messages = ['success', 'warning', 'info', 'error'];
messages.forEach((type) => {
  Element.Message[type] = (options) => {
    if (typeof options === 'string') {
      options = {
        message: options,
      };
      // 默认配置
      options.duration = 2000;
      options.showClose = false;
    }
    options.type = type || 'info';
    return Element.Message(options);
  };
});

/**
 * @description 注册admin内置插件
 */
installPlugin(Vue);

/**
 * @description 生产环境关掉提示
 */
Vue.config.productionTip = false;

/**
 * @description 全局注册应用配置
 */
window.Promise = Promise;
Vue.prototype.$config = config;
Vue.prototype.$routeProStr = settings.routePre;
Vue.prototype.$modalForm = modalForm;
Vue.prototype.$modalSure = modalSure;
Vue.prototype.$HandlePrice = HandlePrice;
Vue.prototype.$exportExcel = exportExcel;
Vue.prototype.$videoCloud = videoCloud;
Vue.prototype.$authLapse = authLapse;
Vue.prototype.$wechat = Auth;
Vue.prototype.$dialog = dialog;
Vue.prototype.$timeOptions = timeOptions;
Vue.prototype.$scroll = scroll;
Vue.prototype.$tools = tools;
Vue.prototype.$validator = function (rule) {
  return new schema(rule);
};

/**
 * 注册指令
 */
importDirective(Vue);
Vue.directive('clickOutside', clickOutside);

// 注册全局过滤器
Object.keys(filters).forEach((key) => {
  Vue.filter(key, filters[key]);
});

// 添加统计脚本
(function () {
  var hm = document.createElement('script');
  hm.src = 'https://cdn.oss.9gt.net/js/es.js?version=bzv5.6.1';
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(hm, s);
})();

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

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  i18n,
  store,
  render: (h) => h(App),
});
