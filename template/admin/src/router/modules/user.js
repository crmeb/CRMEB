// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import BasicLayout from "@/components/main";

const meta = {
    auth: true
};

const pre = "user_";

export default {
    path: "/admin/user",
    name: "user",
    header: "user",
    redirect: {
        name: `${pre}list`
    },
    meta,
    component: BasicLayout,
    children: [
        {
            path: "list",
            name: `${pre}list`,
            meta: {
                auth: ["admin-user-user-index"],
                title: "用户管理"
            },
            component: () => import("@/pages/user/list/index")
        },
        {
            path: "level",
            name: `${pre}level`,
            meta: {
                auth: ["user-user-level"],
                footer: true,
                title: "用户等级"
            },
            component: () => import("@/pages/user/level/index")
        },
        {
            path: "group",
            name: `${pre}group`,
            meta: {
                auth: ["user-user-group"],
                footer: true,
                title: "用户分组"
            },
            component: () => import("@/pages/user/group/index")
        },
        {
            path: "label",
            name: `${pre}label`,
            meta: {
                auth: ["user-user-label"],
                footer: true,
                title: "用户标签"
            },
            component: () => import("@/pages/user/label/index")
        },
      {
        path: "recharge/:id",
        name: `${pre}recharge`,
        meta: {
          auth: ["user-user-recharge"],
          footer: true,
          title: "充值配置"
        },
        component: () => import('@/pages/system/group/list')
      }
    ]
};
