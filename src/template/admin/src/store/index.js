import Vue from 'vue'
import Vuex from 'vuex'
import VuexPersistence from 'vuex-persist'

import user from './module/user'
import app from './module/app'
import menus from './module/menus'
import userInfo from './module/userInfo'
import userLevel from './module/userLevel'
import order from './module/order'
import media from './module/media'
import goodSelect from './module/goodSelect'
import moren from './module/moren'
import shopping from './module/shopping'
import fresh from './module/fresh'

Vue.use(Vuex)
// 持久化储存
// const vuexLocal = new VuexPersistence({
//     storage: window.localStorage,
//
// })

export default new Vuex.Store({
    state: {
        //
    },
    mutations: {
        //
    },
    actions: {
        //
    },
    plugins:[
        new VuexPersistence({
            reducer: state => ({
                user: state.user, //这个就是存入localStorage的值
                app:state.app,
                menus:state.menus,
                userInfo:state.userInfo,
                order:state.order,
                media:state.media
            }),
            storage: window.localStorage
        }).plugin
    ],
    modules: {
        user,
        app,
        menus,
        userInfo,
        order,
        media,
        goodSelect,
        moren,
        shopping,
        fresh,
        userLevel
    }
})
