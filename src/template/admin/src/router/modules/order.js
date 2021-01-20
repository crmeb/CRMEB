import BasicLayout from '@/components/main'

const pre = 'order_'

export default {
  path: '/admin/order',
  name: 'order',
  header: 'order',
  redirect: {
    name: `${pre}list`
  },
  component: BasicLayout,
  children: [
    {
      path: 'list',
      name: `${pre}list`,
      meta: {
        auth: ['admin-order-storeOrder-index'],
        title: '订单管理'
      },
      component: () => import('@/pages/order/orderList/index')
    },
    {
      path: 'offline',
      name: `${pre}offline`,
      meta: {
        auth: ['admin-order-offline'],
        title: '收银订单'
      },
      component: () => import('@/pages/order/offline/index')
    },
    {
      path: 'invoice/list',
      name: `${pre}invoice`,
      meta: {
        auth: ['admin-order-startOrderInvoice-index'],
        title: '发票管理'
      },
      component: () => import('@/pages/order/invoice/index')
    }
  ]
}
