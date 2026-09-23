/**
 * 分销扩展路由配置
 * 兼容模式：既是 Vue Router 路由配置（带 component 的项由 routers.js 扫描注册），
 * 也包含 section/action 元数据供扩展机制使用
 */
import setting from '@/setting';

const routePre = setting.routePre;
const pre = 'agent_';

export default [
  {
    path: 'agent_manage/index',
    section: 'distribution',
    action: 'manage',
    name: `${pre}agentManage`,
    meta: {
      auth: ['agent-agent-manage'],
      title: '分销员管理',
    },
  },
  {
    path: 'spread/apply',
    section: 'distribution',
    action: 'apply',
    name: `${pre}spreadApply`,
    meta: {
      auth: ['admin-agent-spread-apply'],
      title: '分销员申请',
    },
  },
  /** 事业部 */
  {
    path: routePre + '/division',
    name: 'division',
    redirect: { name: 'division_list' },
    component: () => import('@/layout'),
    children: [
      {
        path: 'index',
        section: 'distribution',
        action: 'list',
        name: 'division_list',
        component: () => import('@/pages/system/plugin/PageContainer'),
        props: { typeKey: 'distribution', section: 'distribution', action: 'list' },
        meta: {
          auth: ['agent-division-index'],
          title: '事业部列表',
        },
      },
      {
        path: 'agent/index',
        section: 'distribution',
        action: 'agent',
        name: 'division_agent',
        component: () => import('@/pages/system/plugin/PageContainer'),
        props: { typeKey: 'distribution', section: 'distribution', action: 'agent' },
        meta: {
          auth: ['agent-division-agent-index'],
          title: '代理商列表',
        },
      },
      {
        path: 'agent/statistics',
        section: 'distribution',
        action: 'statistics',
        name: 'division_statistics',
        component: () => import('@/pages/system/plugin/PageContainer'),
        props: { typeKey: 'distribution', section: 'distribution', action: 'statistics' },
        meta: {
          auth: ['agent-division-statistics'],
          title: '事业部统计',
        },
      },
      {
        path: 'agent/applyList',
        section: 'distribution',
        action: 'applyList',
        name: 'division_applyList',
        component: () => import('@/pages/system/plugin/PageContainer'),
        props: { typeKey: 'distribution', section: 'distribution', action: 'applyList' },
        meta: {
          auth: ['agent-division-agent-applyList'],
          title: '代理商申请',
        },
      },
      {
        path: 'agent/agreement',
        section: 'distribution',
        action: 'agreement',
        name: 'division_agreement',
        component: () => import('@/pages/system/plugin/PageContainer'),
        props: { typeKey: 'distribution', section: 'distribution', action: 'agreement' },
        meta: {
          auth: ['agent-division-agent-agreement'],
          title: '代理商规则',
        },
      },
    ],
  },
];
