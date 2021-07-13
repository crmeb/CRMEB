import BasicLayout from '@/components/main'

const pre = 'app_'

export default {
  path: '/admin/app',
  name: 'app',
  header: 'app',
  redirect: {
    name: `${pre}wechatMenus`
  },
  meta: {
    auth: ['admin-app']
  },
  component: BasicLayout,
  children: [
    {
      path: 'wechat/setting/menus/index',
      name: `${pre}wechatMenus`,
      meta: {
        auth: ['application-wechat-menus'],
        title: '微信菜单'
      },
      component: () => import('@/pages/app/wechat/menus/index')
    },
    {
      path: 'wechat/setting/template/index',
      name: `${pre}wechatTemplate`,
      meta: {
        auth: ['application-wechat-template'],
        title: '微信模板消息'
      },
      component: () => import('@/pages/app/routine/routineTemplate/index')
    },
    {
      path: 'wechat/wechat_user/user/index',
      name: `${pre}wechatUser`,
      meta: {
        auth: ['wechat-wechat-user-user'],
        title: '微信用户'
      },
      component: () => import('@/pages/app/wechat/user/user')
    },
    {
      path: 'wechat/wechat_user/user/tag',
      name: `${pre}tag`,
      meta: {
        auth: ['wechat-wechat-user-tag'],
        title: '用户标签'
      },
      component: () => import('@/pages/app/wechat/user/tag')
    },
    {
      path: 'wechat/wechat_user/user/group',
      name: `${pre}group`,
      meta: {
        auth: ['wechat-wechat-user-group'],
        title: '用户分组'
      },
      component: () => import('@/pages/app/wechat/user/tag')
    },
    {
      path: 'wechat/wechat_user/user/message',
      name: `${pre}message`,
      meta: {
        auth: ['wechat-wechat-user-message'],
        title: '用户行为记录'
      },
      component: () => import('@/pages/app/wechat/user/message')
    },
    {
      path: 'wechat/news_category/index',
      name: `${pre}newsCategoryIndex`,
      meta: {
        auth: ['wechat-wechat-news-category-index'],
        title: '图文管理'
      },
      component: () => import('@/pages/app/wechat/newsCategory/index')
    },
    {
      path: 'wechat/news_category/save/:id?',
      name: `${pre}newsCategorySave`,
      meta: {
        auth: ['wechat-wechat-news-category-save'],
        title: '图文添加'
      },
      component: () => import('@/pages/app/wechat/newsCategory/save')
    },
    {
      path: 'wechat/reply/follow/:key',
      name: `${pre}fllow`,
      meta: {
        auth: ['wechat-wechat-reply-subscribe'],
        title: '微信关注回复'
      },
      component: () => import('@/pages/app/wechat/reply/follow')
    },
    {
      path: 'wechat/reply/keyword',
      name: `${pre}keyword`,
      meta: {
        auth: ['wechat-wechat-reply-keyword'],
        title: '关键字回复'
      },
      component: () => import('@/pages/app/wechat/reply/keyword')
    },
    {
      path: 'wechat/reply/keyword/save/:id?',
      name: `${pre}keywordAdd`,
      meta: {
        auth: ['wechat-wechat-reply-save'],
        title: '关键字添加'
      },
      component: () => import('@/pages/app/wechat/reply/follow')
    },
    {
      path: 'wechat/reply/index/:key',
      name: `${pre}replyIndex`,
      meta: {
        auth: ['wechat-wechat-reply-default'],
        title: '无效关键词回复'
      },
      component: () => import('@/pages/app/wechat/reply/follow')
    },
    {
      path: 'routine/routine_template/index',
      name: `${pre}routineTemplate`,
      meta: {
        auth: ['routine-routine_template'],
        title: '小程序订阅消息'
      },
      component: () => import('@/pages/app/routine/routineTemplate/index')
    }
  ]
}
