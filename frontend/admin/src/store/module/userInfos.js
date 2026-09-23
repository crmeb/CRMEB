import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Session } from '@/utils/storage.js';

export const useUserInfosStore = defineStore('userInfos', () => {
  const userInfos = ref({});

  // 设置用户信息
  function getUserInfos(data) {
    userInfos.value = data;
  }

  // 设置用户信息
  async function setUserInfos(data) {
    if (data) {
      getUserInfos(data);
    } else {
      if (Session.get('userInfo')) getUserInfos(Session.get('userInfo'));
    }
  }

  return { userInfos, getUserInfos, setUserInfos };
});
