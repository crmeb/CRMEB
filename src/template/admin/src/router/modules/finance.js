import BasicLayout from '@/components/main'

const pre = 'finance_'
export default {
  path: '/admin/finance',
  name: 'finance',
  header: 'finance',
  meta: {
    // 授权标识
    auth: ['admin-finance']
  },
  redirect: {
    name: `${pre}cashApply`
  },
  component: BasicLayout,
  children: [
    {
      path: 'finance/bill',
      name: `${pre}bill`,
      meta: {
        auth: ['finance-finance-bill'],
        title: '资金记录'
      },
      component: () => import('@/pages/finance/financialRecords/bill')
    }
  ]
}
