<template>
  <div class="goods-box" v-if="defaults.goodsList">
    <div class="acea-row">
      <div class="title">选择商品</div>
      <div class="wrapper">
        <draggable class="dragArea list-group" :list="defaults.goodsList.list" group="peoples" :item-key="goodsItemKey">
          <template #item="{ element: goods, index }">
            <div class="item" v-if="defaults.goodsList.list.length">
              <img :src="goods.image" alt="" />
              <span class="iconfont icondel_1" @click.stop="bindDelete(index)"></span>
            </div>
          </template>
          <template #footer>
            <div class="add-item item" @click="modals = true"><span class="iconfont iconaddto"></span></div>
          </template>
        </draggable>
      </div>
    </div>

    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="900">
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

<script setup>
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';
import goodsList from '@/components/goodsList';

defineOptions({ name: 'c_goods' });

const props = defineProps({
  configObj: {
    type: Object,
  },
});

const modals = ref(false);
const tempGoods = ref({});
const defaults = ref({});

// vuedraggable4 item-key（数据无唯一 id，用索引）
const goodsItemKey = (element) => defaults.value.goodsList.list.indexOf(element);

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
  },
  { immediate: true, deep: true },
);

//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function getProductId(data) {
  // this.tempGoods = data
  modals.value = false;
  let list = defaults.value.goodsList.list.concat(data);
  defaults.value.goodsList.list = unique(list);
}
function cancel() {
  modals.value = false;
  // this.tempGoods = {}
}
function ok() {
  defaults.value.goodsList.list.push(tempGoods.value);
}
function bindDelete(index) {
  defaults.value.goodsList.list.splice(index, 1);
}
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
    .iconfont {
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
      color: #ccc;
      font-size: 22px;
      cursor: pointer;
    }
  }
}
</style>
