<template>
  <div>
    <div class="title-tips" v-if="datas[name].tabList">
      <span>选择模板</span>{{ datas[name].tabList[datas[name].tabVal].name }}
    </div>
    <div class="radio-box" :class="{ on: datas[name].type == 1 }">
      <el-radio-group v-model="datas[name].tabVal" type="button" @change="radioChange()">
        <el-radio-button :label="index" :value="index" v-for="(item, index) in datas[name].tabList" :key="index">
          <span class="iconfont" :class="item.icon" v-if="item.icon"></span>
          <span v-else>{{ item.name }}</span>
        </el-radio-button>
      </el-radio-group>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useMorenStore } from '@diy/store/legacy/moren';

defineOptions({ name: 'c_tab' });

const emit = defineEmits(['getConfig']);

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
  moduleName: {
    type: String,
  },
});

const morenStore = useMorenStore();

const formData = ref({
  type: 0,
});
const defaults = ref({});
const datas = ref(props.configData[props.configNum]);

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
    morenStore.upDataGoodList({ name: props.moduleName, type: datas.value.tabConfig.tabVal });
  },
  { deep: true },
);

function radioChange(e) {
  emit('getConfig', datas.value[props.name].tabVal);
  morenStore.upDataGoodList({ name: props.moduleName, type: datas.value[props.name].tabVal });
}
</script>

<style lang="scss" scoped>
.radio-box {
  :deep(.ivu-radio-group-button) {
    display: flex;
    width: 100%;
    .ivu-radio-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
  &.on {
    :deep(.ivu-radio-group-button) {
      .ivu-radio-wrapper {
        flex: 1;
      }
    }
  }
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
