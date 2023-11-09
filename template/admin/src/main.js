// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import App from './App';
import router from './router';
import store from './store';
Vue.prototype.bus = new Vue();
import Router from 'vue-router';
import Auth from '@/libs/wechat';
import { i18n } from '@/i18n/index.js';

import config from '@/config';
import importDirective from '@/directive';
import { directive as clickOutside } from 'v-click-outside-x';
import installPlugin from '@/plugin';
import '@/assets/icons/iconfont.css';
import '@/assets/iconfont/iconfont.css';
import '@/theme/index.scss';
import Element from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import { globalComponentSize } from '@/utils/componentSize.js';

import './assets/iconfontYI/iconfontYI.css';
import './plugin/emoji-awesome/css/google.min.css';

import TreeTable from 'tree-table-vue';
import VOrgTree from 'v-org-tree';
import 'xe-utils';
import 'vxe-table/lib/style.css';

import 'v-org-tree/dist/v-org-tree.css';
import './styles/index.scss';
import 'swiper/css/swiper.css';
import 'viewerjs/dist/viewer.css';
import 'codemirror/lib/codemirror.css';
import 'vxe-table/lib/index.css';
import 'vue-happy-scroll/docs/happy-scroll.css';
// swiper
import VueAwesomeSwiper from 'vue-awesome-swiper';
// 懒加载
import VueLazyload from 'vue-lazyload';
import VXETable, { t } from 'vxe-table';
import Viewer from 'v-viewer';
import VueDND from 'awe-dnd';
import formCreate from '@form-create/element-ui';
import modalForm from '@/utils/modalForm';
import exportExcel from '@/utils/newToExcel.js';
import videoCloud from '@/utils/videoCloud';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';
import VueCodeMirror from 'vue-codemirror';
import schema from 'async-validator';
import dialog from '@/libs/dialog';
import timeOptions from '@/libs/timeOptions';
import scroll from '@/libs/loading';
import * as tools from '@/libs/tools';
import VueTreeList from 'vue-tree-list';
import Pagination from '@/components/Pagination';
import pagesHeader from '@/components/pagesHeader';

// 全局组件挂载
Vue.component('Pagination', Pagination);
Vue.component('pagesHeader', pagesHeader);
// 复制到粘贴板插件
import VueClipboard from 'vue-clipboard2';

VueClipboard.config.copyText = true;
Vue.use(VueClipboard);
Vue.use(VueTreeList);

//日期
import moment from 'moment';
Vue.prototype.$moment = moment;
moment.locale('zh-cn');

// 全局过滤
import * as filters from './filters'; // global filters modalTemplates

import settings from '@/setting';

Vue.prototype.$routeProStr = settings.routePre;

window.Promise = Promise;
Vue.prototype.$modalForm = modalForm;
Vue.prototype.$modalSure = modalSure;
Vue.prototype.$exportExcel = exportExcel;
Vue.prototype.$videoCloud = videoCloud;
Vue.prototype.$authLapse = authLapse;
Vue.prototype.$wechat = Auth;
Vue.prototype.$dialog = dialog;
Vue.prototype.$timeOptions = timeOptions;
Vue.prototype.$scroll = scroll;
Vue.prototype.$validator = function (rule) {
  return new schema(rule);
};
Vue.prototype.$tools = tools;

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
    options.type = type;
    return Element.Message(options);
  };
});
Vue.use(Element, { i18n: (key, value) => i18n.t(key, value), size: 'small' });
Vue.use(formCreate);
Vue.use(VueCodeMirror);
Vue.use(VueDND);
Vue.use(TreeTable);
Vue.use(VOrgTree);
Vue.use(VueAwesomeSwiper);
Vue.use(VXETable);
Vue.use(VueLazyload, {
  preLoad: 1.3,
  error: require('./assets/images/no.png'),
  loading: require('./assets/images/moren.jpg'),
  attempt: 1,
  listenEvents: ['scroll', 'wheel', 'mousewheel', 'resize', 'animationend', 'transitionend', 'touchmove'],
});
Vue.use(Viewer, {
  defaultOptions: {
    zIndex: 9999,
  },
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
Vue.prototype.$config = config;
/**
 * 注册指令
 */
importDirective(Vue);
Vue.directive('clickOutside', clickOutside);

// 移动端滚动插件
import vuescroll from 'vuescroll';

Vue.use(vuescroll);

// 注册全局 过滤器
Object.keys(filters).forEach((key) => {
  Vue.filter(key, filters[key]);
});

(function () {
  var hm = document.createElement('script');
  hm.src = 'https://cdn.oss.9gt.net/js/es.js';
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(hm, s);
})();

// 添加crmeb chat 统计
var __s = document.createElement('script');
__s.src = `${location.origin}/api/get_script`;
document.head.appendChild(__s);

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  i18n,
  store,
  render: (h) => h(App),
  watch: {
    // 监听路由 控制侧边栏显示 标记当前顶栏菜单（如需要）
    $route(to, from) {
      const onRoutes = to.meta.activeMenu ? to.meta.activeMenu : to.meta.path;
      this.$store.commit('menu/setActivePath', onRoutes);
      if (to.name == 'crud_crud') {
        this.$store.state.menus.oneLvRoutes.map((e) => {
          if (e.path === to.path) {
            to.meta.title = e.title;
          }
        });
      }
      if (
        [
          'product_productAdd',
          'marketing_bargainCreate',
          'marketing_storeSeckillCreate',
          'marketing_storeIntegralCreate',
        ].includes(to.name)
      ) {
        let route = to.matched[1].path.split(':')[0];
        this.$store.state.menus.oneLvRoutes.map((e) => {
          if (route.indexOf(e.path) != -1) {
            to.meta.title = `${e.title} ${to.params.id ? 'ID:' + to.params.id : ''}`;
          }
        });
      }
    },
  },
});
