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
import { ref, computed } from 'vue';

import '@/api/user';

export const useUserStore = defineStore(
  'user',
  () => {
    const userName = ref('');
    const userId = ref('');
    const avatarImgPath = ref('');
    const access = ref('');
    const hasGetInfo = ref(false);
    const unreadCount = ref(0);
    const messageUnreadList = ref([]);
    const messageReadedList = ref([]);
    const messageTrashList = ref([]);
    const messageContentStore = ref({});

    const messageUnreadCount = computed(() => messageUnreadList.value.length);
    const messageReadedCount = computed(() => messageReadedList.value.length);
    const messageTrashCount = computed(() => messageTrashList.value.length);

    function setAvatar(avatarPath) {
      avatarImgPath.value = avatarPath;
    }
    function setUserId(id) {
      userId.value = id;
    }
    function setUserName(name) {
      userName.value = name;
    }
    function setAccess(val) {
      access.value = val;
    }
    function setHasGetInfo(status) {
      hasGetInfo.value = status;
    }
    function setMessageCount(count) {
      unreadCount.value = count;
    }
    function setMessageUnreadList(list) {
      messageUnreadList.value = list;
    }
    function setMessageReadedList(list) {
      messageReadedList.value = list;
    }
    function setMessageTrashList(list) {
      messageTrashList.value = list;
    }
    function updateMessageContentStore({ msg_id, content }) {
      messageContentStore.value[msg_id] = content;
    }
    function moveMsg({ from, to, msg_id }) {
      const listMap = { messageUnreadList, messageReadedList, messageTrashList };
      const index = listMap[from].value.findIndex((_) => _.msg_id === msg_id);
      const msgItem = listMap[from].value.splice(index, 1)[0];
      msgItem.loading = false;
      listMap[to].value.unshift(msgItem);
    }

    // 此方法用来获取未读消息条数，接口只返回数值，不返回消息列表
    function getUnreadMessageCount() {}

    return {
      userName,
      userId,
      avatarImgPath,
      access,
      hasGetInfo,
      unreadCount,
      messageUnreadList,
      messageReadedList,
      messageTrashList,
      messageContentStore,
      messageUnreadCount,
      messageReadedCount,
      messageTrashCount,
      setAvatar,
      setUserId,
      setUserName,
      setAccess,
      setHasGetInfo,
      setMessageCount,
      setMessageUnreadList,
      setMessageReadedList,
      setMessageTrashList,
      updateMessageContentStore,
      moveMsg,
      getUnreadMessageCount,
    };
  },
  { persist: true },
);
