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

export const useMediaStore = defineStore(
  'media',
  () => {
    const isMobile = ref(false); // 是否为手机
    const isTablet = ref(false); // 是否为平板
    const isDesktop = ref(true); // 是否为桌面
    const isFullscreen = ref(false); // 是否切换到了全屏

    /**
     * @description 设置设备类型
     * @param {String} type 设备类型，可选值为 Mobile、Tablet、Desktop
     */
    function setDevice(type) {
      isMobile.value = false;
      isTablet.value = false;
      isDesktop.value = false;
      const device = { Mobile: isMobile, Tablet: isTablet, Desktop: isDesktop }[type];
      if (device) device.value = true;
    }

    return { isMobile, isTablet, isDesktop, isFullscreen, setDevice };
  },
  { persist: true },
);
