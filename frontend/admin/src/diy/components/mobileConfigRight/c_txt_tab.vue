<template>
  <div class="txt_tab" v-if="configData">
    <div class="c_row-item">
      <!-- 同 c_bg_color：裸 el-col 在 Element Plus 下默认 span=24 会撑满整行，改用 div -->
      <div class="c_label">
        {{ configData.title }}
        <span>{{ configData.list[configData.type].val }}</span>
      </div>
      <div class="color-box">
        <el-radio-group v-model="configData.type" type="button" @change="radioChange($event)">
          <el-radio :label="key" :value="key" v-for="(radio, key) in configData.list" :key="key">
            <span class="iconfont" :class="radio.icon" v-if="radio.icon"></span>
            <span v-else>{{ radio.val }}</span>
          </el-radio>
        </el-radio-group>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_txt_tab' });

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

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

function radioChange(e) {
  if (
    configData.value.name !== 'itemSstyle' &&
    configData.value.name !== 'bgStyle' &&
    configData.value.name !== 'conStyle'
  ) {
    emit('getConfig', { name: 'radio', values: e });
  }
}
</script>

<style scoped lang="scss">
.txt_tab {
  margin-top: 20px;
}
.c_row-item {
  margin-bottom: 20px;
}
.row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.iconfont {
  font-size: 18px;
}
</style>
