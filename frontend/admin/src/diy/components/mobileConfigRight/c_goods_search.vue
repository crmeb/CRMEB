<template>
  <!-- 此组件目前没用，留着方便以后开发再用 -->
  <div class="acea-row row-top" style="margin-bottom: 20px" v-if="configData">
    <el-checkbox-group v-model="configData.type" @change="checkboxChange">
      <div>
        <el-checkbox :label="1" :value="1">
          <span>商品分类</span>
        </el-checkbox>
        <el-cascader
          :data="configData.list"
          placeholder="请选择商品分类"
          :props="{ multiple: true, checkStrictly: true, emitPath: false }"
          v-model="configData.activeValue"
          filterable
          @change="sliderChange"
        ></el-cascader>
      </div>
    </el-checkbox-group>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_goods_search' });

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

function checkboxChange(e) {
  emit('getConfig', e);
}
function sliderChange(e) {
  let storage = window.localStorage;
  configData.value.activeValue = e ? e : storage.getItem(timeStamp.value);
  emit('getConfig', { name: 'cascader', values: e });
}
</script>

<style></style>
