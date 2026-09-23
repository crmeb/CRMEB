<template>
  <div class="txt_tab" v-if="datas[name]">
    <div class="c_row-item">
      <el-col class="c_label">
        {{ datas[name].title }}
        <span>{{ datas[name].list[datas[name].type].val }}</span>
      </el-col>
      <el-col class="color-box">
        <el-radio-group v-model="datas[name].type" type="button" @change="radioChange($event)">
          <el-radio-button :label="key" :value="key" v-for="(radio, key) in datas[name].list" :key="key">
            <span class="iconfont" :class="radio.icon" v-if="radio.icon"></span>
            <span v-else>{{ radio.val }}</span>
          </el-radio-button>
        </el-radio-group>
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_txt_tab' });

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
});

const defaults = ref({});
const datas = ref(props.configData[props.configNum]);

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
  },
  { immediate: true, deep: true },
);

function radioChange(e) {
  emit('getConfig', { name: 'radio', values: e });
}
</script>

<style lang="scss" scoped>
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
