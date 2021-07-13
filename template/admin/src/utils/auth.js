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
                const f1p9jvbio3uquwbf1lq = store.state.userInfo.f1p9jvbio3uquwbf1lq

                if (value && value instanceof Array && value.length && f1p9jvbio3uquwbf1lq && f1p9jvbio3uquwbf1lq.length) {
                    const isPermission = includeArray(value, f1p9jvbio3uquwbf1lq)
                    if (!isPermission) {
                        el.parentNode && el.parentNode.removeChild(el)
                    }
                }
            }
        })
    }
}
