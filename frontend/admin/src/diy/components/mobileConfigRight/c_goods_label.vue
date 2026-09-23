<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <div class="labelInput acea-row row-between-wrapper" @click="openStoreLabel">
          <div style="width: 90%">
            <div v-if="configData.list && configData.list.length">
              <el-tag closable v-for="(item, index) in configData.list" :key="index" @close="closeStoreLabel(item)">{{
                item.label_name
              }}</el-tag>
            </div>
            <span class="span" v-else>选择商品标签</span>
          </div>
          <div class="iconfont iconxiayi"></div>
        </div>
      </el-col>
    </div>
    <!-- 商品标签 -->
    <el-dialog v-model="storeLabelShow" title="选择商品标签" width="540">
      <storeLabelList
        v-if="storeLabelShow"
        ref="storeLabel"
        @activeData="activeStoreData"
        @close="storeLabelClose"
      ></storeLabelList>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import storeLabelList from '@/components/storeLabelList';

defineOptions({ name: 'c_goods_label' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
  number: {
    type: null,
  },
});

const emit = defineEmits(['getConfig']);

const storeLabel = ref(null);
const defaults = ref({});
const configData = ref({});
const timeStamp = ref('');
const storeLabelShow = ref(false);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
  });
});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

watch(
  () => props.number,
  (nVal) => {
    timeStamp.value = nVal;
  },
);

function openStoreLabel(row) {
  storeLabelShow.value = true;
  nextTick(() => {
    // 深拷贝配置数据列表，避免直接修改原数据
    const listData = configData.value.list?.length ? JSON.parse(JSON.stringify(configData.value.list)) : undefined;
    // 调用storeLabel方法，传入处理后的数据
    storeLabel.value.storeLabel(listData);
  });
}
function closeStoreLabel(label) {
  if (configData.value.list.length) {
    let index = configData.value.list.indexOf(configData.value.list.filter((d) => d.id == label.id)[0]);
    configData.value.list.splice(index, 1);
    getLabelId(configData.value.list);
  }
}
function activeStoreData(storeDataLabel) {
  storeLabelShow.value = false;
  configData.value.list = storeDataLabel;
  getLabelId(storeDataLabel);
}
function getLabelId(storeDataLabel) {
  let storeActiveIds = [];
  storeDataLabel.forEach((item) => {
    storeActiveIds.push(item.id);
  });
  configData.value.activeValue = storeActiveIds;
  emit('getConfig', { name: 'goodsLabel' });
}
// 标签弹窗关闭
function storeLabelClose() {
  storeLabelShow.value = false;
}
</script>

<style scoped lang="scss">
.slider-box {
  padding: 0 15px;
}
.c_row-item {
  margin-bottom: 20px;
}
.label {
  color: #999999;
  font-size: 12px;
}
.labelInput {
  border: 1px solid #dcdee2;
  width: 100%;
  padding: 0 5px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  .span {
    font-size: 12px;
    color: #c5c8ce;
    padding-left: 10px;
  }
  .iconxiayi {
    font-size: 12px;
  }
}
</style>
