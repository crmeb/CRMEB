// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

/**
 * 布局配置
 * */
import { defineStore } from 'pinia';
import { ref } from 'vue';
import screenfull from 'screenfull';

export const useUserLevelStore = defineStore(
  'userLevel',
  () => {
    const taskId = ref(0);
    const levelId = ref(0);
    const categoryId = ref(0); // 文章分类id

    /**
     * @description 会员任务id
     */
    function getTaskId(val) {
      taskId.value = val;
    }

    /**
     * @description 会员等级id
     */
    function getlevelId(val) {
      levelId.value = val;
    }

    /**
     * @description 文章分类id
     */
    function getCategoryId(val) {
      categoryId.value = val;
    }

    /**
     * @description 初始化监听全屏状态
     * 原版内部 commit('setFullscreen', ...) 指向的 mutation 在本模块从未存在（unknown mutation 警告后 no-op），故移除
     */
    function listenFullscreen() {
      return new Promise((resolve) => {
        if (screenfull.enabled) {
          screenfull.on('change', () => {
            if (!screenfull.isFullscreen) {
              // no-op
            }
          });
        }
        // end
        resolve();
      });
    }
    /**
     * @description 切换全屏
     */
    function toggleFullscreen() {
      return new Promise((resolve) => {
        if (screenfull.isFullscreen) {
          screenfull.exit();
        } else {
          screenfull.request();
        }
        // end
        resolve();
      });
    }

    return { taskId, levelId, categoryId, getTaskId, getlevelId, getCategoryId, listenFullscreen, toggleFullscreen };
  },
  { persist: true },
);
