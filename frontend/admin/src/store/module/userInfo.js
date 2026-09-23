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
import { menusApi } from '@/api/account';

export const useUserInfoStore = defineStore(
  'userInfo',
  () => {
    const userInfo = ref(null);
    const uniqueAuth = ref([]);
    const name = ref('');
    const avatar = ref('');
    const access = ref('');
    const logo = ref('');
    const logoSmall = ref('');
    const version = ref('');
    const newOrderAudioLink = ref('');
    const pageName = ref('');
    //当删除数据并提交时存储默认数据(可视化)
    const uploadListDataswiperBg = ref({});
    const uploadListDatamenus = ref({});
    const uploadListDataactivity = ref({});
    const uploadListDatarecommend = ref({});
    const uploadListDataadsRecommend = ref({});
    const txtListData = ref({});

    //当删除数据并提交时存储默认数据(可视化)
    function uploadListswiperBg(data) {
      uploadListDataswiperBg.value = data;
    }
    function uploadListmenus(data) {
      uploadListDatamenus.value = data;
    }
    function uploadListrecommend(data) {
      uploadListDatarecommend.value = data;
    }
    function uploadListactivity(data) {
      uploadListDataactivity.value = data;
    }
    function uploadListadsRecommend(data) {
      uploadListDataadsRecommend.value = data;
    }
    function txtList(data) {
      txtListData.value = data;
    }
    //
    function setPageName(id) {
      pageName.value = id;
    }
    // 以下 mutation 原名与 state 字段同名（userInfo/uniqueAuth/name/avatar/access/logo/logoSmall/version/newOrderAudioLink），
    // setup store 内函数与 ref 不能同名，统一改名 setXxx
    function setUserInfo(info) {
      userInfo.value = info;
    }
    function userRealName(realName) {
      userInfo.value.real_name = realName;
    }
    function userRealHeadPic(headPic) {
      userInfo.value.head_pic = headPic;
    }
    function setUniqueAuth(val) {
      uniqueAuth.value = val;
    }
    function setName(val) {
      name.value = val;
    }
    function setAvatar(val) {
      avatar.value = val;
    }
    function setAccess(val) {
      access.value = val;
    }
    function setLogo(val) {
      logo.value = val;
    }
    function setLogoSmall(val) {
      logoSmall.value = val;
    }
    function setVersion(val) {
      version.value = val;
    }
    function setNewOrderAudioLink(val) {
      newOrderAudioLink.value = val;
    }

    // 注意（死代码，全库无 dispatch 调用）：原 Vuex 版此 action 1) 未 import menusApi，此处补上 import；
    // 2) 原 commit('getmenusNav', ...) 指向的 mutation 在本模块并不存在（unknown mutation 警告后 no-op），故此处不再调用
    function getMenusNavList() {
      return new Promise((resolve, reject) => {
        menusApi()
          .then(async (res) => {
            resolve(res);
          })
          .catch((res) => {
            reject(res);
          });
      });
    }

    return {
      userInfo,
      uniqueAuth,
      name,
      avatar,
      access,
      logo,
      logoSmall,
      version,
      newOrderAudioLink,
      pageName,
      uploadListDataswiperBg,
      uploadListDatamenus,
      uploadListDataactivity,
      uploadListDatarecommend,
      uploadListDataadsRecommend,
      txtListData,
      uploadListswiperBg,
      uploadListmenus,
      uploadListrecommend,
      uploadListactivity,
      uploadListadsRecommend,
      txtList,
      setPageName,
      setUserInfo,
      userRealName,
      userRealHeadPic,
      setUniqueAuth,
      setName,
      setAvatar,
      setAccess,
      setLogo,
      setLogoSmall,
      setVersion,
      setNewOrderAudioLink,
      getMenusNavList,
    };
  },
  { persist: true },
);
