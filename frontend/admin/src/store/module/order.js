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
import { getOrdes } from '@/api/order';

export const useOrderStore = defineStore(
  'order',
  () => {
    const orderStatus = ref(''); // 订单状态
    const orderTime = ref(''); // 订单时间
    const orderNum = ref('');
    const orderType = ref(0); // 订单状态
    const fieldKey = ref('');
    const orderChartType = ref({});
    const isDels = ref(false);
    const delIdList = ref([]);
    const iconsaaaa = ref('');
    const orderPayType = ref('');
    const real_name = ref('');

    /**
     * @description 订单状态搜索
     */
    function getOrderStatus(val) {
      orderStatus.value = val;
    }

    /**
     * @description 订单状态搜索
     */
    function getOrderType(val) {
      orderPayType.value = val;
    }

    /**
     * @description 时间状态
     */
    function getOrderTime(val) {
      orderTime.value = val;
    }

    /**
     * @description 订单号选择状态
     */
    function getOrderNum(val) {
      orderNum.value = val;
    }

    function getfieldKey(val) {
      fieldKey.value = val;
    }
    /**
     * @description 搜索关键词
     * */
    function setOrderKeyword(keyword) {
      real_name.value = keyword;
    }
    /**
     * @description tab切换，选择订单状态
     */
    function onChangeTabs(val) {
      orderType.value = val;
    }

    /**
     * @description  订单状态 全部对象
     */
    function onChangeChart(val) {
      orderChartType.value = val;
    }

    /**
     * @description  是否可以批量删除订单
     */
    function getIsDel(val) {
      isDels.value = val;
    }

    /**
     * @description  批量删除订单的id集合
     */
    function getisDelIdListl(val) {
      delIdList.value = val;
    }
    function resetSearch() {
      orderType.value = '';
      orderTime.value = '';
      orderNum.value = '';
      orderPayType.value = '';
      real_name.value = '';
    }

    /**
     * @description 订单状态
     */
    function getOrderTabs(data) {
      return new Promise((resolve, reject) => {
        getOrdes(data)
          .then(async (res) => {
            resolve(res);
            onChangeChart(res.data);
          })
          .catch((res) => {
            reject(res);
          });
      });
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

    return {
      orderStatus,
      orderTime,
      orderNum,
      orderType,
      fieldKey,
      orderChartType,
      isDels,
      delIdList,
      iconsaaaa,
      orderPayType,
      real_name,
      getOrderStatus,
      getOrderType,
      getOrderTime,
      getOrderNum,
      getfieldKey,
      setOrderKeyword,
      onChangeTabs,
      onChangeChart,
      getIsDel,
      getisDelIdListl,
      resetSearch,
      getOrderTabs,
      listenFullscreen,
      toggleFullscreen,
    };
  },
  { persist: true },
);
