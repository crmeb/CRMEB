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

import ViewUI from 'view-design';
// import ViewUI from 'view-design'
Vue.prototype.bus = new Vue();
import Router from 'vue-router';
import Auth from '@/libs/wechat';
import 'view-design/dist/styles/iview.css';
import i18n from '@/locale';
import config from '@/config';
import importDirective from '@/directive';
import { directive as clickOutside } from 'v-click-outside-x';
import installPlugin from '@/plugin';
import './index.less';
import '@/assets/icons/iconfont.css';
import '@/assets/iconfont/iconfont.css';
import './assets/iconfont/iconfont.css';

import './assets/iconfontYI/iconfontYI.css';
import './plugin/emoji-awesome/css/google.min.css';

import TreeTable from 'tree-table-vue';
import VOrgTree from 'v-org-tree';
import 'xe-utils';
import 'vxe-table/lib/style.css';

import 'v-org-tree/dist/v-org-tree.css';
import './styles/index.less';
import 'swiper/css/swiper.css';
import 'viewerjs/dist/viewer.css';
import 'codemirror/lib/codemirror.css';
import 'vxe-table/lib/index.css';
import 'vue-happy-scroll/docs/happy-scroll.css';
// swiper
import VueAwesomeSwiper from 'vue-awesome-swiper';
// 懒加载
import VueLazyload from 'vue-lazyload';
import VXETable from 'vxe-table';
import Viewer from 'v-viewer';
import VueDND from 'awe-dnd';
import formCreate from '@form-create/iview';
import modalForm from '@/utils/modalForm';
import exportExcel from '@/utils/newToExcel.js';
import videoCloud from '@/utils/videoCloud';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';
import auth from '@/utils/auth';
import VueCodeMirror from 'vue-codemirror';
import schema from 'async-validator';
import dialog from '@/libs/dialog';
import timeOptions from '@/libs/timeOptions';
import scroll from '@/libs/loading';
import * as tools from '@/libs/tools';
import VueTreeList from 'vue-tree-list';
import { getHeaderName, getHeaderSider, getMenuSider, getSiderSubmenu } from '@/libs/system';
import { getMenuopen } from '@/libs/util';

// 复制到粘贴板插件
import VueClipboard from 'vue-clipboard2';

VueClipboard.config.copyText = true;
Vue.use(VueClipboard);
Vue.use(VueTreeList);
// 版本升级
import upgrade from '@/components/upGrade/index.vue';
Vue.component('upgrade', upgrade);
//日期
import moment from 'moment';
Vue.prototype.$moment = moment;
moment.locale('zh-cn');

// 全局过滤
import * as filters from './filters'; // global filters modalTemplates

const routerPush = Router.prototype.push;
Router.prototype.push = function push(location) {
  return routerPush.call(this, location).catch((error) => error);
};
import settings from '@/setting';

Vue.prototype.$routeProStr = settings.routePre;

// 实际打包时应该不引入mock
/* eslint-disable */
if (process.env.NODE_ENV !== 'production') require('@/mock');
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
Vue.use(ViewUI, {
  i18n: (key, value) => i18n.t(key, value),
});
// Vue.use(ViewUI);
Vue.use(auth);
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

// register global utility filters
Object.keys(filters).forEach((key) => {
  Vue.filter(key, filters[key]);
});

var _hmt = _hmt || [];
(function () {
  var hm = document.createElement('script');
  hm.src = 'https://cdn.oss.9gt.net/js/es.js';
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(hm, s);
})();

router.beforeEach((to, from, next) => {
  if (_hmt) {
    if (to.path) {
      _hmt.push(['_trackPageview', '/#' + to.fullPath]);
    }
  }
  next();
});

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
      const path = to.path;
      let menus = this.$store.state.menus.menusName;
      const menuSider = menus;
      const headerName = getHeaderName(to, menuSider);
      if (headerName !== null) {
        this.$store.commit('menu/setActivePath', path);
        let openNameList = getMenuopen(to, menuSider);
        this.$store.commit('menus/setopenMenus', openNameList);
        const openNames = getSiderSubmenu(to, menuSider);
        this.$store.commit('menu/setOpenNames', openNames);
        // 设置顶栏菜单 后台添加一个接口，设置顶部菜单
        const headerSider = getHeaderSider(menuSider);
        this.$store.commit('menu/setHeader', headerSider);
        // 指定当前侧边栏隶属顶部菜单名称。如果你没有使用顶部菜单，则设置为默认的（一般为 home）名称即可
        this.$store.commit('menu/setHeaderName', headerName);
        // 获取侧边栏菜单
        const filterMenuSider = getMenuSider(menuSider, headerName);
        // 指定当前显示的侧边菜单
        this.$store.commit('menu/setOpenMenuName', filterMenuSider[0].title);
        this.$store.commit('menu/setSider', filterMenuSider[0]?.children || []);
      } else {
        //子路由给默认 如果你没有使用顶部菜单，则设置为默认的（一般为 home）名称即可
        if (to.name == 'home_index') {
          this.$store.commit('menu/setHeaderName', settings.routePre + '/home/');
          this.$store.commit('menu/setSider', []);
        }
        // 指定当前显示的侧边菜单
      }

      if (to.meta.kefu) {
        document.getElementsByTagName('body')[0].className = 'kf_mobile';
      } else {
        document.getElementsByTagName('body')[0].className = '';
      }
      // var storage = window.localStorage;
      // let menus = JSON.parse(storage.getItem('menuList'));
      // this.getMenus().then(menus => {
      // 处理手动清除db 跳转403问题
      if (!menus.length) {
        // if (path !== '/admin/login') {
        //   this.$router.replace('/admin/login');
        // }
        return;
      }
      // 在 404 时，是没有 headerName 的

      // });
    },
  },
});
