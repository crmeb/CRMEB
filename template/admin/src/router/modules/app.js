// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import LayoutMain from '@/layout';
import setting from '@/setting';
import { active } from 'sortablejs';
let routePre = setting.routePre;

const pre = 'app_';

export default {
  path: routePre + '/app',
  name: 'app',
  header: 'app',
  redirect: {
    name: `${pre}wechatMenus`,
  },
  meta: {
    auth: ['admin-app'],
  },
  component: LayoutMain,
  children: [
    {
      path: 'wechat/setting/menus/index',
      name: `${pre}wechatMenus`,
      meta: {
        auth: ['application-wechat-menus'],
        title: '微信菜单',
      },
      component: () => import('@/pages/app/wechat/menus/index'),
    },
    {
      path: 'wechat/wechat_user/user/tag',
      name: `${pre}tag`,
      meta: {
        auth: ['wechat-wechat-user-tag'],
        title: '用户标签',
      },
      component: () => import('@/pages/app/wechat/user/tag'),
    },
    {
      path: 'wechat/wechat_user/user/group',
      name: `${pre}group`,
      meta: {
        auth: ['wechat-wechat-user-group'],
        title: '用户分组',
      },
      component: () => import('@/pages/app/wechat/user/tag'),
    },
    {
      path: 'wechat/wechat_user/user/message',
      name: `${pre}message`,
      meta: {
        auth: ['wechat-wechat-user-message'],
        title: '用户行为记录',
      },
      component: () => import('@/pages/app/wechat/user/message'),
    },
    {
      path: 'wechat/news_category/index',
      name: `${pre}newsCategoryIndex`,
      meta: {
        auth: ['wechat-wechat-news-category-index'],
        title: '图文管理',
      },
      component: () => import('@/pages/app/wechat/newsCategory/index'),
    },
    {
      path: 'wechat/news_category/save/:id?',
      name: `${pre}newsCategorySave`,
      meta: {
        auth: ['wechat-wechat-news-category-save'],
        title: '图文添加',
        activeMenu: routePre + '/app/wechat/news_category/index',
      },
      component: () => import('@/pages/app/wechat/newsCategory/save'),
    },
    {
      path: 'wechat/reply/follow/:key',
      name: `${pre}fllow`,
      meta: {
        auth: ['wechat-wechat-reply-subscribe'],
        title: '微信关注回复',
      },
      component: () => import('@/pages/app/wechat/reply/follow'),
    },
    {
      path: 'wechat/reply/keyword',
      name: `${pre}keyword`,
      meta: {
        auth: ['wechat-wechat-reply-keyword'],
        title: '关键字回复',
      },
      component: () => import('@/pages/app/wechat/reply/keyword'),
    },
    {
      path: 'wechat/reply/keyword/save/:id?',
      name: `${pre}keywordAdd`,
      meta: {
        auth: ['wechat-wechat-reply-save'],
        title: '关键字添加',
        activeMenu: routePre + '/app/wechat/reply/keyword',
      },
      component: () => import('@/pages/app/wechat/reply/follow'),
    },
    {
      path: 'wechat/reply/index/:key',
      name: `${pre}replyIndex`,
      meta: {
        auth: ['wechat-wechat-reply-default'],
        title: '无效关键词回复',
      },
      component: () => import('@/pages/app/wechat/reply/follow'),
    },
    {
      path: 'routine/download',
      name: `${pre}routineTemplate`,
      meta: {
        auth: ['routine-download'],
        title: '小程序下载',
      },
      component: () => import('@/pages/app/routine/download/index'),
    },
    {
      path: 'routine/link',
      name: `${pre}routineLink`,
      meta: {
        auth: ['routine-link'],
        title: '小程序链接',
      },
      component: () => import('@/pages/app/routine/link/index'),
    },
    {
      path: 'app/version',
      name: `${pre}version`,
      meta: {
        auth: ['admin-app-version'],
        title: 'APP版本管理',
      },
      component: () => import('@/pages/app/version/index'),
    },
    {
      path: 'app/agreement',
      name: `${pre}agreement `,
      meta: {
        auth: ['admin-app-agreement'],
        title: '隐私协议',
      },
      component: () => import('@/pages/app/app/index'),
    },
  ],
};
