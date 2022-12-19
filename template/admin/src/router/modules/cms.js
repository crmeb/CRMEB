// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import BasicLayout from '@/components/main';

const pre = 'cms_';

export default {
  path: '/admin/cms',
  name: 'cms',
  header: 'cms',
  redirect: {
    name: `${pre}article`,
  },
  component: BasicLayout,
  children: [
    {
      path: 'article/index/:id?',
      name: `${pre}article`,
      meta: {
        auth: ['cms-article-index'],
        title: '文章管理',
        keepAlive: true,
      },
      component: () => import('@/pages/cms/article/index'),
    },
    {
      path: 'article_category/index',
      name: `${pre}articleCategory`,
      meta: {
        auth: ['cms-article-category'],
        title: '文章分类',
      },
      component: () => import('@/pages/cms/articleCategory/index'),
    },
    {
      path: 'article/add_article/:id?',
      name: `${pre}addArticle`,
      meta: {
        auth: ['cms-article-creat'],
        title: '文章添加',
      },
      component: () => import('@/pages/cms/addArticle/index'),
    },
  ],
};
