<template>
  <div class="goods-box" v-if="defaults.couponList">
    <div class="acea-row">
      <div class="title">选择优惠券</div>
      <div class="wrapper">
        <draggable
          class="dragArea list-group"
          :list="defaults.couponList.list"
          group="coupons"
          handle=".move-icon"
          :item-key="couponItemKey"
        >
          <template #item="{ element: item, index }">
            <div class="item">
              <div class="move-icon">
                <span class="iconfont iconxingzhuangjiehe"></span>
              </div>
              <div class="coupon-item">
                <div class="name line1">{{ item.title }}</div>
              </div>
              <span class="iconfont iconshanchu3" @click.stop="bindDelete(index)"></span>
            </div>
          </template>
        </draggable>
        <div class="add-item" @click="openModal">
          <el-button class="btn"><span class="iconfont iconaddto"></span>添加</el-button>
        </div>
      </div>
    </div>
    <coupon-list ref="couponListRef" :updateIds="ids" @nameId="getCouponId" :status="1" receiveType="all"></coupon-list>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';
import couponList from '@/components/couponList';

defineOptions({ name: 'c_coupon_select' });

const props = defineProps({
  configObj: {
    type: Object,
  },
});

const couponListRef = ref(null);
const defaults = ref({});
const ids = ref([]);

// vuedraggable4 item-key（数据无唯一 id，用索引）
const couponItemKey = (element) => defaults.value.couponList.list.indexOf(element);

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    ids.value = nVal.couponList.list.map((item) => item.id);
  },
  { immediate: true, deep: true },
);

function openModal() {
  couponListRef.value.isTemplate = true;
}
//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function getCouponId(ids, names) {
  couponListRef.value.isTemplate = false;
  // names contains objects with details
  let list = defaults.value.couponList.list.concat(names);
  defaults.value.couponList.list = unique(list);
  ids.value = defaults.value.couponList.list.map((item) => item.id);
}
function bindDelete(index) {
  defaults.value.couponList.list.splice(index, 1);
  ids.value = defaults.value.couponList.list.map((item) => item.id);
}
</script>

<style scoped lang="scss">
.goods-box {
  padding: 0 15px;
  .wrapper {
    flex: 1;
  }
  .wrapper,
  .list-group {
    display: flex;
    flex-direction: column;
    width: 100%;
  }
  .title {
    color: #fff;
    font-size: 12px;
    width: 75px;
    margin-right: 5px;
    margin-top: 20px;
  }
  .add-item {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    border-radius: 4px;
    margin: 0 0px 20px 12px;
    .btn {
      width: 100%;
    }
    .iconfont {
      font-size: 12px;
      color: #999;
      margin-right: 5px;
    }
    .tip {
      color: #333;
    }
  }
  .item {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    height: 40px;
    margin-bottom: 4px;
    background: #fff;
    border-radius: 4px;
    padding: 0 10px;
    box-sizing: border-box;
    font-size: 13px;
    &.sortable-chosen,
    &.sortable-drag,
    &.sortable-ghost {
      background: #f7f7f7 !important;
    }
    .move-icon {
      margin-right: 10px;
      .iconfont {
        cursor: move;
        color: #ddd;
        font-size: 14px;
      }
    }

    .coupon-item {
      flex: 1;
      display: flex;
      align-items: center;
      overflow: hidden;
      max-width: 210px;
      .name {
        font-size: 12px;
        color: #333;
        width: 100%;
      }
    }

    .iconshanchu3 {
      display: none;
      color: #999999;
      font-size: 16px;
      cursor: pointer;
      margin-left: 10px;
    }
    &:hover {
      .iconshanchu3 {
        display: block;
      }
    }
  }
}
</style>
