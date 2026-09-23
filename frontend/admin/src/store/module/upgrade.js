// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUpgradeStore = defineStore(
  'upgrade',
  () => {
    const toggleStatus = ref(false);

    // 原 getter toggleStatus 为恒等 getter（返回 state.toggleStatus 本身），且与 state 字段同名，
    // setup store 内无法并存，直接使用 store.toggleStatus 即可（值完全一致）
    function TOGGLE_STATUS(status) {
      toggleStatus.value = status;
    }

    return { toggleStatus, TOGGLE_STATUS };
  },
  { persist: true },
);
