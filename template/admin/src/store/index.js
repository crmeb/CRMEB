// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

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
import kefu from './module/kefu'
import integralOrder from './module/integralOrder'

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
                userLevel:state.userLevel,
                order:state.order,
                media:state.media,
                kefu:state.kefu,
                integralOrder:state.integralOrder
            }),
            storage: window.localStorage
        }).plugin
    ],
    modules: {
        user,
        app,
        menus,
        userInfo,
        userLevel,
        order,
        media,
        goodSelect,
        moren,
        shopping,
        fresh,
        kefu,
        integralOrder
    }
})
