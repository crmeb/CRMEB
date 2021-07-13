import BasicLayout from '@/components/main'

const pre = 'agent_'
const meta = {
    auth: true
}
export default {
    path: '/admin/agent',
    name: 'agent',
    header: 'agent',
    redirect: {
        name: `${pre}agentManage`
    },
    meta,
    component: BasicLayout,
    children: [
        {
            path: 'agent_manage/index',
            name: `${pre}agentManage`,
            meta: {
                auth: ['agent-agent-manage'],
                title: '分销员管理'
            },
            component: () => import('@/pages/agent/agentManage')
        }
    ]
}
