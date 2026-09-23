<template>
  <div style="margin-bottom: 20px">
    <div class="title-tips" v-if="configData.tabList">
      <span>{{ configData.title }}</span
      >{{ configData.tabList[configData.tabVal].name }}
    </div>
    <div class="radio-box" :class="{ on: configData.type == 1 }">
      <el-radio-group v-model="configData.tabVal" type="button" size="large" @change="radioChange($event)">
        <el-radio :label="index" :value="index" v-for="(item, index) in configData.tabList" :key="index">
          <span class="iconfont" :class="item.icon" v-if="item.icon"></span>
          <span v-else>{{ item.name }}</span>
        </el-radio>
      </el-radio-group>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_tab' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const formData = ref({
  type: 0,
});
const defaults = ref({});
const configData = ref({});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
  });
});

function radioChange(e) {
  if (defaults.value.picStyle) {
    defaults.value.picStyle.tabVal = 0;
  }
  emit('getConfig', e);
}
</script>

<style scoped lang="scss">
.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper:after {
  height: 0;
}
.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper {
  height: 32px;
  width: 120px;
  text-align: center;
}
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
.iconfont {
  font-size: 20px;
  line-height: 18px;
}
</style>
