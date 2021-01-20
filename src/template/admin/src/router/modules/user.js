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
        }
    ]
};
