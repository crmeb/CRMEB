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
 * 布局配置
 * */
import screenfull from 'screenfull'
// import router from '@/router';
// import Setting from '@/setting';

export default {
  namespaced: true,
  state: {
    taskId: 0,
    levelId: 0,
    categoryId: 0 // 文章分类id
  },
  mutations: {
    /**
         * @description 设置设备类型
         * @param {Object} state vuex state
         * @param {String} type 设备类型，可选值为 Mobile、Tablet、Desktop
         */

    /**
         * @description 会员任务id
         */
    getTaskId (state, taskId) {
      state.taskId = taskId
    },

    /**
         * @description 会员等级id
         */
    getlevelId (state, levelId) {
      state.levelId = levelId
    },

    /**
         * @description 文章分类id
         */
    getCategoryId (state, categoryId) {
      state.categoryId = categoryId
    }
  },
  actions: {
    /**
         * @description 初始化监听全屏状态
         */
    listenFullscreen ({ commit }) {
      return new Promise(resolve => {
        if (screenfull.enabled) {
          screenfull.on('change', () => {
            if (!screenfull.isFullscreen) {
              commit('setFullscreen', false)
            }
          })
        }
        // end
        resolve()
      })
    },
    /**
         * @description 切换全屏
         */
    toggleFullscreen ({ commit }) {
      return new Promise(resolve => {
        if (screenfull.isFullscreen) {
          screenfull.exit()
          commit('setFullscreen', false)
        } else {
          screenfull.request()
          commit('setFullscreen', true)
        }
        // end
        resolve()
      })
    }
  }
}
