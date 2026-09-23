<template>
  <div class="divBox">
    <div>
      <div class="box-container">
        <div class="list sp">
          <label class="name">商品名称：</label>
          <span class="info">{{ FormData.name }}</span>
        </div>
        <div class="list sp">
          <label class="name">直播价：</label>
          <span class="info">{{ FormData.price }}</span>
        </div>
        <div class="list sp100" style="display: flex" v-if="FormData.product">
          <label class="name">商品图：</label>
          <div v-viewer>
            <img :src="FormData.product.image" alt="" />
          </div>
        </div>
        <div class="list sp100">
          <label class="name">审核结果：</label>
          <span class="info">{{ $filters.liveStatusFilter(FormData.audit_status) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'LiveGoodsDetail' });

defineProps({
  FormData: {
    type: Object,
    default: () => ({}),
  },
});
</script>

<style scoped>
.box-container {
  overflow: hidden;
}
.box-container .list {
  float: left;
  line-height: 40px;
  margin-bottom: 20px;
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
.box-container .list img {
  width: 80px;
  height: 80px;
}
.box-container .list .blue {
  color: var(--prev-color-primary);
}
</style>
