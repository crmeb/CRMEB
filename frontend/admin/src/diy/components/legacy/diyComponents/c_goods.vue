<template>
  <div class="goods-box" v-if="defaults.goodsList">
    <div class="wrapper">
      <draggable class="dragArea list-group" :list="defaults.goodsList.list" group="peoples" :item-key="goodsItemKey">
        <template #item="{ element: goods, index }">
          <div class="item" v-show="defaults.goodsList.list.length">
            <img :src="type == 1 ? goods.pic : goods.image" alt="" />
            <span class="iconfont icondel_1" v-db-click @click.stop="bindDelete(index)"></span>
          </div>
        </template>
        <template #footer>
          <div class="add-item item" v-db-click @click="modals = true"><span class="iconfont iconaddto"></span></div>
        </template>
      </draggable>
    </div>

    <el-dialog v-model="modals" :title="titles" class="paymentFooter" :class="type ? '' : 'middleTop'" width="900px">
      <sort-list ref="goodslist" @getProductDiy="getProductDiy" v-if="modals && type == 1"></sort-list>
      <goods-list
        ref="goodslist"
        @getProductDiy="getProductDiy"
        :ischeckbox="true"
        :type="type"
        :is_new="is_new"
        :diy="true"
        v-if="modals && type != 1"
      ></goods-list>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="ok">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import draggable from 'vuedraggable';
import goodsList from '@/components/goodsList';
import sortList from '@diy/components/legacy/sortList';

defineOptions({ name: 'c_goods' });

const props = defineProps({
  name: {
    type: String,
  },
  configData: {
    type: null,
  },
  configNum: {
    type: [Number, String],
    default: 'default',
  },
});

const modals = ref(false);
const tempGoods = ref([]);
const defaults = ref({});
const type = ref('');
const is_new = ref('');
const loading = ref(true);
const titles = ref('');

watch(
  () => props.configData,
  (nVal) => {
    defaults.value = nVal[props.configNum];
    let goodType = nVal[props.configNum].titleInfo ? nVal[props.configNum].titleInfo.type : 0;
    type.value = nVal[props.configNum].selectConfig.type
      ? nVal[props.configNum].selectConfig.type
      : goodType
      ? goodType
      : 0;
    switch (type.value) {
      case 0:
        titles.value = '商品列表';
        break;
      case 1:
        titles.value = '分类列表';
        break;
      case 8:
        titles.value = '砍价列表';
        break;
      case 2:
        titles.value = '秒杀列表';
        break;
      case 3:
        titles.value = '拼团列表';
        break;
      default:
    }
  },
  { immediate: true, deep: true },
);

// created
defaults.value = props.configData[props.configNum];
is_new.value = props.configData[props.configNum].is_new;

// vuedraggable4 item-key（数据无唯一 id，用索引）
function goodsItemKey(element) {
  return defaults.value.goodsList.list.indexOf(element);
}

function getProductDiy(data) {
  tempGoods.value = data;
  loading.value = false;
}
function cancel() {
  tempGoods.value = [];
}
//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function ok() {
  if (!tempGoods.value.length) {
    return ElMessage.warning('请先选择商品');
  }
  let list = defaults.value.goodsList.list;
  list.push.apply(list, tempGoods.value);
  // list.push(tempGoods.value);
  let picList = unique(list);
  defaults.value.goodsList.list = picList;
  // defaults.value.goodsList.list.push(tempGoods.value);
  modals.value = false;
}
function bindDelete(index) {
  defaults.value.goodsList.list.splice(index, 1);
}
</script>

<style lang="scss" scoped>
.middleTop :deep(.ivu-modal-wrap .ivu-modal) {
  top: 50% !important;
  margin-top: -350px;
}
.goods-box {
  padding: 16px 0;
  margin-bottom: 16px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  .wrapper,
  .list-group {
    display: flex;
    flex-wrap: wrap;
  }
  .add-item {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    margin-bottom: 10px;
    background: #f7f7f7;
    .iconfont {
      font-size: 18px;
      color: #d8d8d8;
    }
  }
  .item {
    position: relative;
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
    margin-right: 12px;
    &:nth-child(4n) {
      margin-right: 0;
    }
    img {
      width: 100%;
      height: 100%;
    }
    .icondel_1 {
      position: absolute;
      right: -10px;
      top: -16px;
      color: #ccc;
      font-size: 28px;
      cursor: pointer;
    }
  }
}
</style>
