<template>
  <div class="goods-box" v-if="defaults.goodsList">
    <div class="acea-row">
      <div class="title">选择商品</div>
      <div class="wrapper">
        <draggable class="dragArea list-group" :list="defaults.goodsList.list" group="peoples">
          <div
            class="item"
            v-for="(goods, index) in defaults.goodsList.list"
            :key="index"
            v-if="defaults.goodsList.list.length"
          >
            <img :src="goods.image" alt="" />
            <span class="iconfont-diy icondel_1" @click.stop="bindDelete(index)"></span>
          </div>
          <div class="add-item item" @click="modals = true"><span class="iconfont-diy iconaddto"></span></div>
        </draggable>
      </div>
    </div>

    <el-dialog
      :visible.sync="modals"
      title="商品列表"
      class="paymentFooter"
      width="900"
    >
      <goods-list
        ref="goodslist"
        :ischeckbox="true"
        :isdiy="true"
        isType
        @getProductId="getProductId"
        v-if="modals"
      ></goods-list>
    </el-dialog>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import goodsList from '@/components/goodsList';
export default {
  name: 'c_goods',
  props: {
    configObj: {
      type: Object,
    },
  },
  components: {
    goodsList,
    draggable: vuedraggable,
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
      },
      immediate: true,
      deep: true,
    },
  },
  data() {
    return {
      modals: false,
      goodsList: [],
      tempGoods: {},
      defaults: {},
    };
  },
  created() {
    this.defaults = this.configObj;
  },
  methods: {
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    getProductId(data) {
      // this.tempGoods = data
      this.modals = false;
      let list = this.defaults.goodsList.list.concat(data);
      this.defaults.goodsList.list = this.unique(list);
    },
    cancel() {
      this.modals = false;
      // this.tempGoods = {}
    },
    ok() {
      this.defaults.goodsList.list.push(this.tempGoods);
    },
    bindDelete(index) {
      this.defaults.goodsList.list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="scss">
.goods-box {
  padding: 0 15px;
  .wrapper,
  .list-group {
    display: flex;
    flex-wrap: wrap;
    width: 272px;
  }
  .title {
    color: #999999;
    font-size: 12px;
    width: 75px;
    margin-right: 16px;
    margin-top: 20px;
  }
  .add-item {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 1px solid #eee;
    .iconfont-diy {
      font-size: 24px;
      color: #d8d8d8;
    }
  }
  .item {
    position: relative;
    width: 64px;
    height: 64px;
    margin-bottom: 20px;
    margin-right: 12px;
    &:nth-of-type(3n) {
      margin-right: 0;
    }
    img {
      width: 100%;
      height: 100%;
    }
    .icondel_1 {
      position: absolute;
      right: -5px;
      top: -12px;
      color: #999999;
      font-size: 22px;
      cursor: pointer;
    }
  }
}
</style>
