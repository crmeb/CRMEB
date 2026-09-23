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
import { AccountLogoutKefu } from '@/pages/plugin/kefu/api';
import { getCookies, removeCookies, setCookies } from '@/libs/util';
import router from '@/router';
import { Socket } from '@/pages/plugin/kefu/libs/socket';
import Setting from '@/setting';

export const useKefuStore = defineStore(
  'kefu',
  () => {
    const kefuInfo = ref(null);

    function setInfo(val) {
      kefuInfo.value = val;
    }

    /**
     * @description 退出登录
     * */
    function logoutKefu({ confirm = false, vm } = {}) {
      async function logout() {
        AccountLogoutKefu()
          .then(() => {
            Socket.then((ws) => {
              ws.send({
                type: 'logout',
                data: { uid: getCookies('kefu_uuid') },
              });
            });
            removeCookies('kefu_token');
            removeCookies('kefu_expires_time');
            removeCookies('kefuInfo');
            removeCookies('kefu_uuid');
            // 跳转路由（客服登录页路径带 routePre 前缀）
            router.push({
              path: Setting.routePre + '/kefu',
            });
          })
          .catch((res) => {
            console.log(res);
          });
      }
      logout();
    }

    return { kefuInfo, setInfo, logoutKefu };
  },
  { persist: true },
);
