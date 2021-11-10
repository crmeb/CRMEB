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
import Router from 'vue-router'
import routes from './routers'
import Setting from '@/setting'
import store from '@/store'
import iView from 'iview'
import { removeCookies, getCookies, setTitle } from '@/libs/util'
import { includeArray } from '@/libs/auth'

Vue.use(Router)

const router = new Router({
    routes,
    mode: Setting.routerMode
})
/**
 * 路由拦截
 * 权限验证
 */

router.beforeEach(async (to, from, next) => {
    if(to.fullPath.indexOf('kefu') != -1){
        return next()
    }
    // if (Setting.showProgressBar) iView.LoadingBar.start()
    // 判断是否需要登录才可以进入
    if (to.matched.some(_ => _.meta.auth)) {
        // 这里依据 token 判断是否登录，可视情况修改
        const token = getCookies('token')
        if (token && token !== 'undefined') {
            const access = store.state.userInfo.uniqueAuth
            const isPermission = includeArray(to.meta.auth, access)
            if (isPermission) {
                next()
            } else {
                if(access.length == 0){
                    next({
                        name: 'login',
                        query: {
                            redirect: to.fullPath
                        }
                    })
                    localStorage.clear()
                    removeCookies('token')
                    removeCookies('expires_time')
                    removeCookies('uuid')
                }else{
                    next({
                        name: '403'
                    })
                }
            }
            // next();
        } else {
            // 没有登录的时候跳转到登录界面
            // 携带上登陆成功之后需要跳转的页面完整路径
            next({
                name: 'login',
                query: {
                    redirect: to.fullPath
                }
            })
            localStorage.clear()
            removeCookies('token')
            removeCookies('expires_time')
            removeCookies('uuid')
        }
    } else {
        // 不需要身份校验 直接通过
        next()
    }
})
router.afterEach(to => {
    // if (Setting.showProgressBar) iView.LoadingBar.finish()
    // 更改标题
    setTitle(to, router.app)
    // 返回页面顶端
    window.scrollTo(0, 0)
})

export default router
