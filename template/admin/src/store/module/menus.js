// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

/**
 * 布局菜单配置
 * */
import { menusApi } from '@/api/account'
function getMenusName () {
    let storage = window.localStorage
    let af1g6po2m8qkr6nlba7f1l = JSON.parse(storage.getItem('menuList'))
    if (typeof af1g6po2m8qkr6nlba7f1l !== 'object' || af1g6po2m8qkr6nlba7f1l === null) {
        af1g6po2m8qkr6nlba7f1l = []
    }
    return af1g6po2m8qkr6nlba7f1l
}
export default {
    namespaced: true,
    state: {
        menusName: getMenusName(),
        openMenus: []
    },
    mutations: {
        getmenusNav (state, af1g6po2m8qkr6nlba7f1l) {
            state.menusName = af1g6po2m8qkr6nlba7f1l
        },
        // getopenMenus (state, openList) {
        //   state.openMenus = openList
        // }
        setopenMenus (state, openList) {
            state.openMenus = openList
        }
    },
    actions: {
        getMenusNavList ({ commit }) {
            return new Promise((resolve, reject) => {
                menusApi().then(async res => {
                    resolve(res)
                    commit('getmenusNav', res.data.menus)
                }).catch(res => {
                    reject(res)
                })
            })
        }
    }
}
