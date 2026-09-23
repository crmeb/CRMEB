<template>
  <div class="slider-box">
    <div class="c_row-item" v-if="configData.title">
      <el-col class="label" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-cascader
          @change="sliderChange"
          placeholder="请选择品牌"
          v-model="configData.brandVal"
          :options="brandData"
          :props="cascaderProps"
          filterable
          clearable
        >
        </el-cascader>
      </el-col>
    </div>
  </div>
</template>

<script setup>
// import { brandList } from '@/api/product';
import { ref, watch, onMounted, nextTick } from 'vue';
import { ElMessage } from 'element-plus';

defineOptions({ name: 'c_brand' });

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

const defaults = ref({});
const configData = ref({});
const cascaderProps = ref({ emitPath: false, multiple: true });
const brandData = ref([]);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    // getBrandList();
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

function sliderChange() {
  emit('getConfig', { name: 'brands' });
}
function getBrandList() {
  brandList()
    .then((res) => {
      brandData.value = res.data;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
</script>

<style scoped lang="scss">
.slider-box {
  padding: 0 15px;
  .label {
    color: #999999;
    font-size: 12px;
    width: 75px;
    margin-right: 16px;
  }
}
.c_row-item {
  margin-bottom: 20px;
}
:deep(.el-cascader__search-input) {
  margin-left: 8px;
}
:deep(.el-cascader) {
  width: 100%;
}
</style>
