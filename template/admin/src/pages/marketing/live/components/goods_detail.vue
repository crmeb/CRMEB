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
          <span class="info">{{ FormData.audit_status | liveStatusFilter }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { liveGoodsDetail } from '@/api/live';
export default {
  name: 'live_detail',
  data() {
    return {
      option: {
        form: {
          labelWidth: '150px',
        },
      },
      FormData: {},
      loading: false,
    };
  },
  methods: {
    getData(id) {
      this.loading = true;
      liveGoodsDetail(id)
        .then((res) => {
          this.FormData = res.data;
          this.loading = false;
        })
        .catch((error) => {
          this.$Message.error(error.msg);
          this.loading = false;
        });
    },
  },
};
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
  color: #1890ff;
}
.box-container .list.image {
  margin-bottom: 40px;
}
.box-container .list.image img {
  position: relative;
  top: 40px;
}
.el-textarea {
  width: 400px;
}
</style>
