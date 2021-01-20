import BasicLayout from '@/components/main'

const pre = 'product_'

export default {
  path: '/admin/product',
  name: 'product',
  header: 'product',
  meta: {
    title: '商品',
    // 授权标识
    auth: ['admin-store-index']
  },
  redirect: {
    name: `${pre}productList`
  },
  component: BasicLayout,
  children: [
    {
      path: 'product_list',
      name: `${pre}productList`,
      meta: {
        title: '商品管理',
        auth: ['admin-store-storeProuduct-index']
      },
      component: () => import('@/pages/product/productList')
    },
    {
      path: 'product_classify',
      name: `${pre}productClassify`,
      meta: {
        title: '商品分类',
        auth: ['admin-store-storeCategory-index']
      },
      component: () => import('@/pages/product/productClassify')
    },
    {
      path: 'add_product/:id?',
      name: `${pre}productAdd`,
      meta: {
        auth: ['admin-store-storeProuduct-index'],
        title: '商品添加'
      },
      component: () => import('@/pages/product/productAdd')
    },
    {
      path: 'product_reply/:id?',
      name: `${pre}productEvaluate`,
      meta: {
        auth: ['admin-store-storeProuduct-index'],
        title: '商品评论'
      },
      component: () => import('@/pages/product/productReply')
    },
    {
      path: 'product_attr',
      name: `${pre}productAttr`,
      meta: {
        auth: ['admin-store-storeProuduct-index'],
        title: '商品规格'
      },
      component: () => import('@/pages/product/productAttr')
    }
  ]
}
