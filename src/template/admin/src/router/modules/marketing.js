import BasicLayout from '@/components/main'
const pre = 'marketing_'

export default {
    path: '/admin/marketing',
    name: 'marketing',
    header: 'marketing',
    redirect: {
        name: `${pre}storeCoupon`
    },
    component: BasicLayout,
    children: [

        {
            path: 'store_coupon/index',
            name: `${pre}storeCoupon`,
            meta: {
                auth: ['marketing-store_coupon'],
                title: '优惠券模板'
            },
            component: () => import('@/pages/marketing/storeCoupon/index')
        },
        {
            path: 'store_coupon_issue/index',
            name: `${pre}storeCouponIssue`,
            meta: {
                auth: ['marketing-store_coupon_issue'],
                title: '优惠券列表'
            },
            component: () => import('@/pages/marketing/storeCouponIssue/index')
        },
        {
            path: 'store_coupon_issue/create/:id?',
            name: `${pre}storeCouponCreate`,
            meta: {
                auth: ['admin-marketing-store_coupon_issue-create'],
                title: '添加优惠券'
            },
            component: () => import('@/pages/marketing/storeCouponIssue/create')
        },
        {
            path: 'store_coupon_user/index',
            name: `${pre}storeCouponUser`,
            meta: {
                auth: ['marketing-store_coupon_user'],
                title: '用户领取记录'
            },
            component: () => import('@/pages/marketing/storeCouponUser/index')
        },
        {
            path: 'coupon/system_config/:type?/:tab_id?',
            name: `${pre}coupon`,
            meta: {
                auth: ['admin-order-storeOrder-index'],
                title: '优惠券配置'
            },
            component: () => import('@/pages/setting/setSystem/index')
        },

        {
            path: `integral/system_config/:type?/:tab_id?`,
            name: `${pre}integral`,
            meta: {
                auth: ['marketing-integral-system_config'],
                title: '积分配置'
            },
            component: () => import('@/pages/setting/setSystem/index')
        },
        {
            path: 'user_point/index',
            name: `${pre}userPoint`,
            meta: {
                auth: ['marketing-user_point'],
                title: '积分日志'
            },
            component: () => import('@/pages/marketing/userPoint/index')
        }
    ]
}
