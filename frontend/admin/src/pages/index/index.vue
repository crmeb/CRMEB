<template>
  <div class="dashboard-home">
    <base-info />
    <grid-menu v-if="userInfo?.level == 0" />
    <visit-chart />
    <user-chart />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import baseInfo from './components/baseInfo';
import gridMenu from './components/gridMenu';
import visitChart from './components/visitChart';
import userChart from './components/userChart';
import { auth as authApi } from '@/api/system';
import { useUserInfoStore } from '@/store/module/userInfo';

defineOptions({ name: 'index' });

const userInfoStore = useUserInfoStore();
const userInfo = computed(() => userInfoStore.userInfo);

onMounted(() => {
  authApi().catch(() => {});
});
</script>

<style lang="scss" scoped>
.dashboard-home {
  :deep(.ivu-mb) {
    margin-bottom: 16px;
  }
}
</style>
