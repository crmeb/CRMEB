<template>
  <div class="txt_tab" v-if="configData">
    <div class="c_row-item">
      <!-- 同 c_bg_color：裸 el-col 在 Element Plus 下默认 span=24 会撑满整行，改用 div -->
      <div class="c_label">
        {{ configData.title }}
        <span>{{ list[configData.val].val }}</span>
      </div>
      <div class="c_radio-group">
        <el-radio-group v-model="configData.val" type="button" @change="radioChange($event)">
          <el-radio-button :label="item.key" :value="item.key" v-for="(item, key) in list" :key="key">
            <span class="iconfont" :class="item.icon"></span>
          </el-radio-button>
        </el-radio-group>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_align' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const defaults = ref({});
const configData = ref({});
const list = ref({
  left: {
    val: '居左',
    icon: 'iconzuoduiqi',
    key: 'left',
  },
  center: {
    val: '居中',
    icon: 'iconjuzhongduiqi',
    key: 'center',
  },
  right: {
    val: '居右',
    icon: 'iconyouduiqi',
    key: 'right',
  },
});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

function radioChange(e) {
  emit('getConfig', { name: 'radio', values: e });
}
</script>

<style scoped lang="scss">
.txt_tab {
  // margin-top: 20px;
}
.c_row-item {
  margin: 0 15px 20px;
}
.c_radio-group {
  width: 70%;
}
.row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.iconfont {
  font-size: 18px;
}
:deep(.el-radio-button__inner) {
  padding: 5px 10px;
}
</style>
