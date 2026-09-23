<template>
  <div class="box" :class="configData.type == 'form' ? '' : 'on3'" v-if="configData">
    <div class="c_row-item" :class="{ on: configData.type == 'form', on2: configData.type == 'ranges' }">
      <el-col class="label" :span="configData.type == 'ranges' ? '' : 4">
        {{ configData.title }}
      </el-col>
      <el-col :span="configData.type == 'ranges' ? 24 : configData.type == 'form' ? 19 : 18" class="slider-box">
        <el-input v-model="configData.value" :placeholder="configData.place" :maxlength="configData.max">
          <template #suffix
            ><i
              v-if="configData.title == '链接' || configData.title == '按钮链接'"
              class="el-icon-link"
              @click="getLink(configData)"
          /></template>
        </el-input>
      </el-col>
    </div>
    <linkaddress
      ref="linkaddres"
      @linkUrl="linkUrl"
      v-if="configData.type != 'form' && (configData.title == '链接' || configData.type == 'link')"
    ></linkaddress>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'c_input_item' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const linkaddres = ref(null);
const value = ref('');
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

function linkUrl(e) {
  configData.value.value = e;
}
function getLink(configData) {
  if (configData.title != '链接' && configData.title != '按钮链接' && configData.type != 'link') {
    return;
  }
  linkaddres.value.modals = true;
}
</script>

<style scoped lang="scss">
:deep(.ivu-input) {
  font-size: 13px !important;
}

.box {
  &.on3 {
    padding: 0 15px;

    :deep(.ivu-input) {
      font-size: 12px !important;
    }

    .label {
      font-size: 12px;
      color: #999;
    }

    :deep(.ivu-input-icon) {
      color: #bbbbbb;
    }
  }
}

.c_row-item {
  margin-bottom: 10px;

  &.on {
    margin-bottom: 20px;

    .label {
      text-align: right;
      color: #666;
    }
  }

  &.on2 {
    display: block;

    .label {
      margin-bottom: 5px;
      color: #666;
    }
  }
}
</style>
