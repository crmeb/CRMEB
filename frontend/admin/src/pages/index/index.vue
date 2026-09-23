<template>
  <div>
    <!-- <div class="open-image" v-db-click @click="clear" v-if="openImage">
      <img src="@/assets/images/wechat_demo.png" alt="" />
    </div> -->
    <!--头部-->
    <base-info />
    <!--小方块-->
    <grid-menu v-if="userInfo?.level == 0" />
    <!--订单统计-->
    <visit-chart />
    <!--用户-->
    <user-chart />
    <!--版本升级-->
    <!-- <upgrade v-if="force_reminder == 1" /> -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import baseInfo from './components/baseInfo';
import gridMenu from './components/gridMenu';
import visitChart from './components/visitChart';
import userChart from './components/userChart';
import { auth as authApi } from '@/api/system';
import { useUserInfoStore } from '@/store/module/userInfo';

defineOptions({ name: 'index' });

const userInfoStore = useUserInfoStore();

const openImage = ref(false);
const visitType = ref('day'); // day, month, year
const visitDate = ref([new Date(), new Date()]);
const force_reminder = ref(null);
let authCode = '';
let auth = false;

const userInfo = computed(() => userInfoStore.userInfo);

onMounted(() => {
  getAuth();
});

function getAuth() {
  authApi()
    .then((res) => {
      let data = res.data || {};
      force_reminder.value = data.force_reminder;
      if (data.auth_code && data.auth) {
        authCode = data.auth_code;
        auth = true;
      }
      openImage.value = true;
    })
    .catch((res) => {});
}
function clear() {
  openImage.value = false;
}
</script>

<style lang="scss">
.dashboard-console-visit {
  .ivu-radio-group-button .ivu-radio-wrapper {
    border: none !important;
    box-shadow: none !important;
    padding: 0 12px;
  }
  .ivu-radio-group-button .ivu-radio-wrapper:before,
  .ivu-radio-group-button .ivu-radio-wrapper:after {
    display: none;
  }
}
.open-image {
  transition: none;
  animation: none;
  display: flex;
  align-items: center;
  justify-content: center;
  position: fixed;
  background-color: rgba(0, 0, 0, 0.6);
  height: 100vh;
  width: 100vw;
  top: 0;
  left: 0;
  z-index: 1000;
  img {
    width: 800px;
  }
}
</style>
