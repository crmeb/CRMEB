<template>
  <div class="slider-box">
    <div class="c_row-item" v-if="configData.title">
      <el-col class="label" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-cascader
          @change="sliderChange"
          placeholder="请选择分类"
          v-model="modelValue"
          :options="treeSelect"
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
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { cascaderListApi } from '@/api/product';
import { categoryListApi } from '@/api/cms';

defineOptions({ name: 'c_classify' });

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
const cascaderProps = ref({ multiple: true, checkStrictly: true, emitPath: false });
const treeSelect = ref([]);

const modelValue = computed({
  get() {
    if (configData.value.activeValue !== undefined) {
      return configData.value.activeValue;
    }
    return configData.value.classVal;
  },
  set(val) {
    if (configData.value.activeValue !== undefined) {
      configData.value.activeValue = val;
    } else {
      configData.value.classVal = val;
    }
  },
});

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    if (props.configNme === 'articleClass') {
      articleCategory();
    } else {
      goodsCategory();
    }
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
  emit('getConfig', { name: 'classlfy' });
}
function articleCategory() {
  categoryListApi({
    status: 1,
    type: 1,
  })
    .then((res) => {
      treeSelect.value = formatCategory(res.data);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function formatCategory(list) {
  return list.map((item) => {
    return {
      value: item.id,
      label: item.title,
      children: item.children ? formatCategory(item.children) : null,
    };
  });
}
function goodsCategory() {
  cascaderListApi(1)
    .then((res) => {
      treeSelect.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style scoped lang="scss">
.slider-box {
  margin-top: 20px;
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
  font-size: 12px;
}
:deep(.el-cascader) {
  width: 100%;
  .el-tag {
    margin: 4px 0 2px 6px;
  }
}
</style>
