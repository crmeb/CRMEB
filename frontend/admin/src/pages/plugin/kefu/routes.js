/** 客服插件路由配置。客服页面与其路由均归属插件目录，便于独立维护和构建。 */
import setting from '@/setting';
import Layout from '@/layout';

const routePre = setting.routePre;
const pre = 'kefu_';
const meta = { auth: true, kefu: true };

// 客服工作台/移动端页面：独立全屏路由（无系统布局），供客服端跳转使用
// 注意：load 必须是静态可分析的 () => import('...')，Vite 构建期才能收集模块（禁止 import(`./${x}`) 全动态写法）
// 注意：path 统一拼接 routePre（/admin），与 Vite base、生产部署路径一致；否则 dev server 不服务该路径，生产也走不到 admin 站点
const fullPage = (path, title, load, extra = {}) => ({
  path: routePre + path,
  name: `${pre}${extra.name || path.replace(/[^a-zA-Z0-9]+/g, '_')}`,
  meta: { ...meta, title, ...extra.meta },
  component: load,
});

// 后台管理页面：包在系统布局内显示系统菜单（path 与菜单 menu_path 拼接 routePre 后一致）
const framePage = (path, title, load, extra = {}) => ({
  path,
  meta: { ...meta, title, ...extra.meta },
  component: Layout,
  children: [
    {
      path: '',
      name: `${pre}${extra.name || path.replace(/[^a-zA-Z0-9]+/g, '_')}`,
      component: load,
      meta: { ...meta, title, ...extra.meta },
    },
  ],
});

export default [
  // ========== 客服工作台/访客端（独立全屏，不显示系统菜单） ==========
  fullPage('/kefu', '客服管理', () => import('./index'), { name: 'index' }),
  fullPage('/kefu/mobile_list', '消息列表', () => import('./mobile/chat_list'), { name: 'mobile_list' }),
  fullPage('/kefu/mobile_chat', '对话详情', () => import('./mobile/index'), { name: 'mobile_chat' }),
  fullPage('/kefu/pc_list', '客服', () => import('./pc/index'), { name: 'pc_list' }),
  fullPage('/kefu/user/index/:uid?/:type?', '客户信息', () => import('./mobile/user/index'), {
    name: 'user-index',
  }),
  fullPage('/kefu/appChat', '客服', () => import('./appChat/index'), { name: 'app-chat' }),

  // 兼容：/admin/kefu 即客服工作台入口（由上方 fullPage('/kefu') 拼接 routePre 得到）

  // ========== 后台管理（系统布局内，显示系统菜单） ==========
  framePage(routePre + '/setting/store_service/index', '客服管理', () => import('./storeService/index'), {
    name: 'service',
    meta: { auth: ['setting-store-service'] },
  }),
  framePage(routePre + '/setting/store_service/speechcraft', '客服话术', () => import('./storeService/speechcraft'), {
    name: 'speechcraft',
    meta: { auth: ['admin-setting-store_service-speechcraft'] },
  }),
  framePage(routePre + '/setting/store_service/feedback', '用户留言', () => import('./storeService/feedback'), {
    name: 'feedback',
    meta: { auth: ['admin-setting-store_service-feedback'] },
  }),
  framePage(routePre + '/setting/store_service/auto_reply', '自动回复', () => import('./storeService/autoReply'), {
    name: 'auto_reply',
    meta: { auth: ['admin-setting-store_service-auto_reply'] },
  }),
  framePage(routePre + '/setting/store_service/config', '客服配置', () => import('./storeService/config'), {
    name: 'service_config',
    meta: { auth: ['admin-setting-store_service-config'] },
  }),
];
