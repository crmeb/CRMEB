import setting from '@/setting';

const routePre = setting.routePre;

export default [
  {
    path: routePre + '/aiagent',
    component: () => import('@/layout'),
    children: [
      {
        path: 'index',
        section: 'aiagent',
        action: 'manage',
        name: 'aiagent_manage',
        meta: { auth: ['aiagent-manage'], title: 'AI 对话' },
        component: () => import('./pages/index'),
      },
    ],
  },
];
