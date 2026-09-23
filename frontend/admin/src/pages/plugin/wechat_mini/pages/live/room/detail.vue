<template>
  <div class="divBox">
    <div>
      <div class="box-container">
        <div class="list sp">
          <label class="name">直播间名称：</label>
          <span class="info">{{ FormData.name }}</span>
        </div>
        <div class="list sp">
          <label class="name">主播微信号：</label>
          <span class="info">{{ FormData.anchor_wechat }}</span>
        </div>
        <div class="list sp">
          <label class="name">直播间ID：</label>
          <span class="info">{{ FormData.id }}</span>
          <span class="info">（微信直播间ID：{{ FormData.room_id }}）</span>
        </div>
        <div class="list sp">
          <label class="name">主播昵称：</label>
          <span class="info">{{ FormData.anchor_name }}</span>
        </div>
        <div class="list sp">
          <label class="name">手机号：</label>
          <span class="info">{{ FormData.phone }}</span>
        </div>
        <div class="list sp">
          <label class="name">直播状态：</label>
          <span class="info">{{ $filters.liveReviewStatusFilter(FormData.live_status) }}</span>
        </div>
        <div class="list sp">
          <label class="name">直播开始时间：</label>
          <span class="info">{{ FormData.start_time }}</span>
        </div>
        <div class="list sp">
          <label class="name">直播结束时间：</label>
          <span class="info">{{ FormData.end_time }}</span>
        </div>
        <div class="list sp">
          <label class="name">直播间类型：</label>
          <span class="info">{{ $filters.broadcastType(FormData.type) }}</span>
        </div>
        <div class="list sp">
          <label class="name">显示类型：</label>
          <span class="info">{{ $filters.broadcastDisplayType(FormData.screen_type) }}</span>
        </div>
        <div class="list sp image">
          <label class="name">背景图：</label>
          <img style="max-width: 150px; height: 80px" :src="FormData.cover_img" />
        </div>
        <div class="list sp image">
          <label class="name">分享图：</label>
          <img style="max-width: 150px; height: 80px" :src="FormData.share_img" />
        </div>
        <div class="list sp">
          <label class="name">是否开启点赞：</label>
          <span class="info blue">{{ $filters.filterClose(FormData.close_like) }}</span>
        </div>
        <div class="list sp">
          <label class="name">是否开启货架：</label>
          <span class="info blue">{{ $filters.filterClose(FormData.close_goods) }}</span>
        </div>
        <div class="list sp">
          <label class="name">是否开启评论：</label>
          <span class="info blue">{{ $filters.filterClose(FormData.close_comment) }}</span>
        </div>
        <div class="list">
          <label class="name">是否显示直播回放：</label>
          <span class="info blue">{{ $filters.filterClose(FormData.replay_status) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { liveDetailApi } from '../../../api';

defineOptions({ name: 'LiveRoomDetail' });

const option = ref({
  form: {
    labelWidth: '150px',
  },
});
const FormData = ref({});
const loading = ref(false);

function getData(id) {
  loading.value = true;
  liveDetailApi(id)
    .then((res) => {
      FormData.value = res.data;
      loading.value = false;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
      loading.value = false;
    });
}

defineExpose({ getData });
</script>

<style scoped>
.box-container {
  overflow: hidden;
}
.box-container .list {
  float: left;
  line-height: 40px;
}
.box-container .sp {
  width: 50%;
}
.box-container .sp3 {
  width: 33.3333%;
}
.box-container .sp100 {
  width: 100%;
}
.box-container .list .name {
  display: inline-block;
  width: 150px;
  text-align: right;
  color: #606266;
}
.box-container .list .blue {
  color: var(--prev-color-primary);
}
.box-container .list.image {
  margin-bottom: 40px;
}
.box-container .list.image img {
  position: relative;
  top: 40px;
}
</style>
