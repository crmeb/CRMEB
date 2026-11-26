import setting from '@/setting';
import Layout from '@/layout';

const routePre = setting.routePre;

export default [
  {
    path: routePre + '/aiagent',
    component: Layout,
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
