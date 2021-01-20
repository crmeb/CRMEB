/**
 * 布局菜单配置
 * */
import { menusApi } from '@/api/account'
function getMenusName () {
    let storage = window.localStorage
    let menuList = JSON.parse(storage.getItem('menuList'))
    if (typeof menuList !== 'object' || menuList === null) {
        menuList = []
    }
    return menuList
}
export default {
    namespaced: true,
    state: {
        menusName: getMenusName(),
        openMenus: []
    },
    mutations: {
        getmenusNav (state, menuList) {
            state.menusName = menuList
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
