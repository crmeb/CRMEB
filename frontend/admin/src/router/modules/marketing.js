// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import LayoutMain from '@/layout';
import setting from '@/setting';
let routePre = setting.routePre;

const pre = 'marketing_';

export default {
  path: routePre + '/marketing',
  name: 'marketing',
  header: 'marketing',
  redirect: {
    name: `${pre}storeCouponIssue`,
  },
  component: LayoutMain,
  children: [
    // ===== 优惠券管理 =====
    {
      path: 'store_coupon_issue/index',
      name: `${pre}storeCouponIssue`,
      meta: {
        auth: ['marketing-store_coupon_issue'],
        title: '优惠券列表',
        keepAlive: true,
      },
      component: () => import('@/pages/marketing/storeCoupon/index'),
    },
    {
      path: 'store_coupon_issue/create/:id?/:edit?',
      name: `${pre}storeCouponCreate`,
      meta: {
        auth: ['marketing-store_coupon_issue-create'],
        title: '添加优惠券',
        activeMenu: routePre + '/marketing/store_coupon_issue/index',
      },
      component: () => import('@/pages/marketing/storeCoupon/create'),
    },
    {
      path: 'store_coupon_user/index',
      name: `${pre}storeCouponUser`,
      meta: {
        auth: ['marketing-store_coupon_user'],
        title: '用户领取记录',
      },
      component: () => import('@/pages/marketing/storeCoupon/user'),
    },
    // 砍价路由已迁移至插件 bargain/，兼容旧路径由插件 routes.js 处理
    // ===== 积分管理（系统内置） =====
    // 积分记录、积分统计的 auth 值与数据库 unique_auth 保持一致
    {
      path: 'integral/record',
      name: `${pre}integralRecord`,
      meta: {
        auth: ['marketing-point_record-index'],
        title: '积分记录',
        activeMenu: routePre + '/marketing/integral/record',
      },
      component: () => import('@/pages/marketing/integral/record/index.vue'),
    },
    {
      path: 'integral/statistics',
      name: `${pre}integralStatistics`,
      meta: {
        auth: ['marketing-point_statistic-index'],
        title: '积分统计',
        activeMenu: routePre + '/marketing/integral/statistics',
      },
      component: () => import('@/pages/marketing/integral/statistics/index.vue'),
    },
    // ===== 直播管理（已迁移至插件 wechat_mini/，由插件 routes.js 注册） =====
    // ===== 渠道码（已迁移至插件 channel_code/，由插件 routes.js 注册） =====
  ],
};
