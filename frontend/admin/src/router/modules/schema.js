// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

/**
 * 通用页面通配路由
 *
 * 一条路由承接所有插件的通用管理页面：
 * 访问 /admin/plugin/{pluginKey}/{pageKey}（如 /admin/plugin/mcp/list），
 * 由 SchemaPage 容器按路由参数拉取页面配置并渲染。
 *
 * 插件无需注册任何前端路由：Installer 菜单的 menu_path 直接写约定路径即可。
 * 注意：与既有 /admin/plugin/list（插件管理）、/admin/plugin/recharge/... 等静态路由
 * 无冲突（vue-router 4 静态段评分高于动态参数段）。
 */
import LayoutMain from '@/layout';
import setting from '@/setting';

const routePre = setting.routePre;

export default {
  path: routePre + '/plugin/:pluginKey/:pageKey',
  name: 'SchemaPluginPage',
  meta: {
    title: '插件页面',
  },
  component: LayoutMain,
  children: [
    {
      path: '',
      name: 'SchemaPluginPageView',
      meta: {
        auth: true,
        title: '插件页面',
      },
      component: () => import('@/components/SchemaPage/index.vue'),
      props: (route) => ({
        pluginKey: route.params.pluginKey,
        pageKey: route.params.pageKey,
      }),
    },
  ],
};
