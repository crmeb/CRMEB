<template>
  <div class="c_radio mb15" v-if="configData">
    <div class="c_row-item" :class="{ on: configData.type == 'ranges' }">
      <el-col class="c_label on" :span="configData.type == 'ranges' ? '' : 4">
        {{ configData.title }}
      </el-col>
      <el-col class="color-box" :span="configData.type == 'ranges' ? 24 : 19">
        <div>
          <el-radio-group v-model="configData.tabVal" @change="radioChange()">
            <el-radio :label="key" :value="key" v-for="(radio, key) in configData.tabList" :key="key">
              <span>{{ radio.name }}</span>
            </el-radio>
          </el-radio-group>
        </div>
        <div>
          <el-radio-group v-model="configData.tabData" @change="radioDataChange()" v-if="configData.tabVal == 0">
            <el-radio :label="key" :value="key" v-for="(radio, key) in configData.dataList" :key="key + 'data'">
              <span>{{ radio.name }}</span>
            </el-radio>
          </el-radio-group>
        </div>
        <el-date-picker
          type="date"
          v-model="configData.specifyDate"
          placeholder="请选择"
          style="margin-top: 6px; width: 100%"
          v-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'data'"
        />
        <el-time-picker
          format="HH:mm"
          v-model="configData.specifyDate"
          placeholder="请选择"
          style="margin-top: 6px; width: 100%"
          v-else-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'time'"
        />
        <el-date-picker
          type="daterange"
          placement="bottom-end"
          v-model="configData.specifyDate"
          format="YYYY/MM/DD"
          placeholder="请选择"
          style="margin-top: 6px; width: 100%"
          v-else-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'daterange'"
          @change="getDaterange"
        />
        <el-time-picker
          format="HH:mm"
          is-range
          v-model="configData.specifyDate"
          placement="bottom-end"
          placeholder="请选择"
          style="margin-top: 6px; width: 100%"
          v-else-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'timerange'"
        />
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_comb_data' });

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

function getDaterange(e) {
  emit('getConfig', { type: 2, val: e });
}
function radioChange(e) {
  emit('getConfig', { type: 0, val: e });
}
function radioDataChange(e) {
  emit('getConfig', { type: 1, val: e });
}
</script>

<style scoped lang="scss">
.ivu-date-picker {
  width: 100%;
}
.c_radio {
  .c_row-item {
    align-items: unset;
    &.on {
      display: block;
      .c_label {
        text-align: left;
        margin-bottom: 3px;
      }
    }
  }
  .c_label {
    color: #000;
    margin-right: 15px;
    margin-top: 4px;
    &.on {
      text-align: right;
      color: #666;
    }
  }
  :deep(.ivu-radio-wrapper) {
    margin: 5px 25px 5px 0;
  }
  :deep(.ivu-radio) {
    margin-right: 6px;
  }
}
</style>
