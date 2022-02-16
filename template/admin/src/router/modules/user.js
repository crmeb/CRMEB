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
      },
        {
            path: "grade/type",
            name: `${pre}type`,
            meta: {
                auth: ["admin-user-member-type"],
                footer: true,
                title: "会员类型"
            },
            component: () => import("@/pages/user/grade/type/index")
        },
        {
            path: "grade/card",
            name: `${pre}card`,
            meta: {
                auth: ["admin-user-grade-card"],
                footer: true,
                title: "卡密会员"
            },
            component: () => import("@/pages/user/grade/card/index")
        },
        {
            path: "grade/record",
            name: `${pre}record`,
            meta: {
                auth: ["admin-user-grade-record"],
                footer: true,
                title: "会员记录"
            },
            component: () => import("@/pages/user/grade/record/index")
        },
        {
            path: "grade/right",
            name: `${pre}right`,
            meta: {
                auth: ["admin-user-grade-right"],
                footer: true,
                title: "会员权益"
            },
            component: () => import("@/pages/user/grade/right/index")
        },
        {
            path: "grade/list/:id",
            name: `${pre}gradelist`,
            meta: {
                auth: ["user-member_card-index"],
                footer: true,
                title: "会员卡列表"
            },
            component: () => import("@/pages/user/grade/card/list")
        },
        {
            path: "grade/agreement",
            name: `${pre}agreement`,
            meta: {
                auth: ["admin-user-grade-agreement"],
                footer: true,
                title: "会员协议"
            },
            component: () => import("@/pages/user/grade/agreement/index")
        }
    ]
};
