<template>
  <div>
    <div class="c_row-item" v-if="configData">
      <el-col span="8" class="c_label">{{ configData.title }}</el-col>
      <el-col span="14" class="color-box">
        <el-switch v-model="configData.status" @change="change" />
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_status' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const mobildConfigStore = useMobildConfigStore();

const defaults = ref({});
const configData = ref({
  status: false,
});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

function change(status) {
  nextTick(() => {
    configData.value.status = status;
    mobildConfigStore.footStatus(status);
  });
  //   emit('getConfig', configData.value);
}
</script>

<style scoped lang="scss">
.c_row-item {
  margin-top: 10px;
  margin-bottom: 20px;
}

.color-box {
  display: flex;
  align-items: center;
  justify-content: flex-end;

  .color-item {
    margin-left: 15px;

    span {
      margin-left: 5px;
      color: #999;
      font-size: 13px;
      cursor: pointer;
    }
  }
}
</style>
