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
 * @description 鉴权指令
 * 当传入的权限当前用户没有时，会移除该组件
 * 用例：<div v-auth="['admin']">text</div>
 * */
import store from '@/store'
import { includeArray } from '@/libs/auth'
export default {
    async install (Vue, options) {
        Vue.directive('auth', {
            inserted (el, binding, vnode) {
                const { value } = binding
                const access = store.state.userInfo.access

                if (value && value instanceof Array && value.length && access && access.length) {
                    const isPermission = includeArray(value, access)
                    if (!isPermission) {
                        el.parentNode && el.parentNode.removeChild(el)
                    }
                }
            }
        })
    }
}
