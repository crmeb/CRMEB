<template>
  <div>
    <div class="item">
      <span>直播间名称：</span>
      {{ datas.name }}
    </div>
    <div class="item">
      <span>直播间ID：</span>
      {{ datas.id }}
    </div>
    <div class="item box">
      <div class="box-item" v-for="(item, index) in goodsList" :key="index">
        <img :src="item.image" alt="" />
        <i class="el-icon-close" v-db-click @click="bindDelete(index)" style="font-size: 20px"></i>
      </div>
      <div class="upload-box" v-db-click @click="modals = true">
        <i class="el-icon-picture-outline" style="font-size: 24px"></i>
      </div>
    </div>
    <el-button type="primary" style="width: 100%" v-db-click @click="bindSub">提交</el-button>
    <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list
        ref="goodslist"
        @getProductId="getProductId"
        v-if="modals"
        :ischeckbox="true"
        :liveStatus="true"
      ></goods-list>
    </el-dialog>
  </div>
</template>

<script>
import goodsList from '@/components/goodsList';
export default {
  name: 'add_goods',
  components: {
    goodsList,
  },
  props: {
    datas: {
      type: Object,
      default: function () {
        return {};
      },
    },
  },
  data() {
    return {
      modals: false,
      goodsList: [],
    };
  },
  methods: {
    getProductId(data) {
      this.goodsList = this.goodsList.concat(data);
      this.$nextTick((res) => {
        setTimeout(() => {
          this.modals = false;
        }, 300);
      });
    },
    bindDelete(index) {
      this.goodsList.splice(index, 1);
    },
    bindSub() {
      let arr = [];
      this.goodsList.map((el) => {
        arr.push(el.product_id);
      });
      this.$emit('getData', arr);
    },
  },
};
</script>

<style lang="scss" scoped>
.item {
  margin-bottom: 10px;
}
.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background: #ccc;
}
.box {
  display: flex;
  flex-wrap: wrap;
  .box-item {
    position: relative;
    margin-right: 20px;
    .ivu-icon {
      position: absolute;
      right: -10px;
      top: -8px;
      color: #999;
      cursor: pointer;
    }
  }
  .upload-box,
  .box-item {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
    img {
      width: 100%;
      height: 100%;
    }
  }
}
</style>
