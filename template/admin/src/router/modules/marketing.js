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
            path: 'store_combination/index',
            name: `${pre}combinalist`,
            meta: {
                auth: ['marketing-store_combination'],
                title: '拼团商品'
            },
            component: () => import('@/pages/marketing/storeCombination/index')
        },
        {
            path: 'store_combination/combina_list',
            name: `${pre}combinaList`,
            meta: {
                auth: ['marketing-store_combination-combina_list'],
                title: '拼团列表'
            },
            component: () => import('@/pages/marketing/storeCombination/combinaList')
        },
        {
            path: 'store_combination/create/:id?/:copy?',
            name: `${pre}storeCombinationCreate`,
            meta: {
                auth: ['marketing-store_combination-create'],
                title: '添加拼团'
            },
            component: () => import('@/pages/marketing/storeCombination/create')
        },
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
            path: 'store_bargain/index',
            name: `${pre}storeBargain`,
            meta: {
                auth: ['marketing-store_bargain'],
                title: '砍价商品'
            },
            component: () => import('@/pages/marketing/storeBargain/index')
        },
        {
            path: 'store_bargain/bargain_list',
            name: `${pre}bargainList`,
            meta: {
                auth: ['marketing-store_bargain-bargain_list'],
                title: '砍价列表'
            },
            component: () => import('@/pages/marketing/storeBargain/bargainList')
        },
        {
            path: 'store_bargain/create/:id?/:copy?',
            name: `${pre}storeBargainCreate`,
            meta: {
                auth: ['marketing-store_bargain-create'],
                title: '添加砍价'
            },
            component: () => import('@/pages/marketing/storeBargain/create')
        },
        {
            path: 'store_seckill/index',
            name: `${pre}storeSeckill`,
            meta: {
                auth: ['marketing-store_seckill'],
                title: '秒杀商品'
            },
            component: () => import('@/pages/marketing/storeSeckill/index')
        },
        {
            path: 'store_seckill_data/index/:id',
            name: `${pre}storeSeckillData`,
            meta: {
                auth: ['marketing-store_seckill-data'],
                title: '秒杀配置'
            },
            component: () => import('@/pages/system/group/list')
        },
        {
            path: 'store_seckill/create/:id?/:copy?',
            name: `${pre}storeSeckillCreate`,
            meta: {
                auth: ['marketing-store_seckill-create'],
                title: '添加秒杀'
            },
            component: () => import('@/pages/marketing/storeSeckill/create')
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
        },
        {
            path: 'live/live_room',
            name: `${pre}live_room`,
            meta: {
                auth: true,
                title: '直播间管理'
            },
            component: () => import('@/pages/marketing/live/index')
        },
        {
            path: 'live/add_live_room',
            name: `${pre}add_live_room`,
            meta: {
                auth: true,
                title: '直播间管理'
            },
            component: () => import('@/pages/marketing/live/creat_live')
        },
        {
            path: 'live/live_goods',
            name: `${pre}live_goods`,
            meta: {
                auth: true,
                title: '直播间商品管理'
            },
            component: () => import('@/pages/marketing/live/live_goods')
        },
        {
            path: 'live/add_live_goods',
            name: `${pre}add_live_goods`,
            meta: {
                auth: true,
                title: '直播间商品管理'
            },
            component: () => import('@/pages/marketing/live/add_goods')
        },
        {
            path: 'live/anchor',
            name: `${pre}anchor`,
            meta: {
                auth: true,
                title: '主播管理'
            },
            component: () => import('@/pages/marketing/live/anchor')
        }
    ]
}
