<template>
  <div class="checkboxs acea-row row-top">
    <div class="title-tips" v-if="configData">
      <span>{{ configData.title }}</span>
    </div>
    <div class="checkbox-box">
      <el-checkbox-group v-model="configData.type" @change="checkboxChange()">
        <el-checkbox
          :value="item.id"
          :disabled="
            (selectedData.length >= 3 && userStyle && configData.userType && !selectedData.includes(item.id)) ||
            (configData.type.length >= 3 && configData.name == 'showContent' && !configData.type.includes(item.id)) ||
            (configData.type.length >= configData.maxList && !configData.type.includes(item.id))
              ? true
              : false
          "
          v-for="(item, index) in configData.list"
          :key="index"
        >
          <span>{{ item.name }}</span>
        </el-checkbox>
      </el-checkbox-group>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_checkbox' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const formData = ref({
  type: 0,
});
const defaults = ref({});
const configData = ref({});
const selectedData = ref([]);
const userStyle = ref(0);

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    userStyle.value = nVal.styleConfig?.tabVal || 1;
    selectedData.value = nVal.checkboxInfo?.type || [];
  },
  { deep: true },
);

watch(
  () => props.configObj?.styleConfig?.tabVal,
  () => {
    if (configData.value.userType) {
      configData.value.type = [3, 1, 2];
    }
  },
);

watch(
  () => props.configObj?.storeStyleConfig?.tabVal,
  (nVal) => {
    if (configData.value.storeType) {
      if (nVal == 1) {
        configData.value.list = [
          {
            id: 0,
            name: '配送方式',
          },
          {
            id: 2,
            name: '门店距离',
          },
          {
            id: 3,
            name: '门店地址',
          },
        ];
      } else {
        configData.value.list = [
          {
            id: 0,
            name: '配送方式',
          },
          {
            id: 1,
            name: '营业时间',
          },
          {
            id: 2,
            name: '门店距离',
          },
          {
            id: 3,
            name: '门店地址',
          },
        ];
      }
    }
  },
);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    userStyle.value = defaults.value.styleConfig?.tabVal;
    selectedData.value = defaults.value.checkboxInfo?.type || [];
  });
});

function checkboxChange(e) {
  // emit('getConfig', e);
}
</script>

<style scoped lang="scss">
.checkboxs {
  padding: 0 15px;
  margin-bottom: 20px;
}
.title-tips {
  margin-right: 14px;
  color: #999;
  font-size: 12px;
  width: 82px;
}
.checkbox-box {
  width: 270px;
}
.ivu-checkbox-group-item {
  margin-bottom: 15px;
  margin-right: 15px;
}
:deep(.ivu-checkbox) {
  margin-right: 4px;
}
:deep(.ivu-checkbox-group-item) {
  font-size: 12px;
}
:deep(.ivu-checkbox-wrapper:nth-last-child(1)) {
  margin-right: 0;
}
</style>
