<template>
  <div class="wechat-mini-app">
    <div class="section-content">
      <component :is="sectionComponent" :params="params" />
    </div>
  </div>
</template>

<script setup>
// 小程序下载/上传/链接
import { computed } from 'vue';
import RoutineDownload from './pages/routine/download/index.vue';
import RoutineCiUpload from './pages/routine/ciUpload/index.vue';
import RoutineLink from './pages/routine/link/index.vue';
// 直播
import RoomList from './pages/live/room/index.vue';
import RoomCreate from './pages/live/room/create.vue';
import GoodsList from './pages/live/goods/list.vue';
import GoodsAdd from './pages/live/goods/add.vue';
import AnchorList from './pages/live/anchor/list.vue';

defineOptions({ name: 'WechatMiniApp' });

const props = defineProps({
  section: { type: String, default: 'wechat_mini' },
  action: { type: String, default: 'download' },
  params: { type: Object, default: () => ({}) },
});

const actionComponents = {
  download: RoutineDownload,
  ci_upload: RoutineCiUpload,
  link: RoutineLink,
  room_list: RoomList,
  room_create: RoomCreate,
  goods_list: GoodsList,
  goods_add: GoodsAdd,
  anchor_list: AnchorList,
};

const sectionComponent = computed(() => actionComponents[props.action] || RoutineDownload);
</script>

<style scoped>
.wechat-mini-app {
  height: 100%;
}
</style>
