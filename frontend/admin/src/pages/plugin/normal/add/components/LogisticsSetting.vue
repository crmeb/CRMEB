<template>
  <!--  物流设置 -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="物流方式：" prop="delivery_methods">
        <el-checkbox-group v-model="computedDeliveryMethods">
          <el-checkbox value="express">快递</el-checkbox>
          <el-checkbox value="pickup">到店</el-checkbox>
          <el-checkbox value="door_service">上门服务</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
    </el-col>
    <template v-if="hasExpress">
      <el-col :span="24">
        <el-form-item label="运费设置：">
          <el-radio-group v-model="computedExpressType">
            <el-radio label="free" value="free">包邮</el-radio>
            <el-radio label="fixed" value="fixed">固定邮费</el-radio>
            <el-radio label="template" value="template">运费模板</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-col>
      <el-col :span="24" v-if="expressType === 'fixed'">
        <el-form-item label="">
          <div class="acea-row">
            <el-input-number
              :controls="false"
              :min="0"
              :model-value="expressPostage"
              @change="onExpressPostageChange"
              placeholder="请输入金额"
              class="input_width maxW input-number-unit-class"
              class-unit="元"
            />
          </div>
        </el-form-item>
      </el-col>
      <el-col :span="24" v-if="expressType === 'template'">
        <el-form-item label="" prop="templateId">
          <div class="acea-row">
            <el-select v-model="computedExpressTempId" clearable placeholder="请选择运费模板" class="input_width maxW">
              <el-option v-for="(item, index) in templateList" :value="item.id" :key="index" :label="item.name" />
            </el-select>
            <span class="addfont" v-db-click @click="addTemp">新增运费模板</span>
          </div>
        </el-form-item>
      </el-col>
    </template>
    <template v-if="hasDoorService">
      <el-col :span="24">
        <el-form-item label="上门服务费：">
          <el-radio-group v-model="computedDoorServiceType">
            <el-radio label="free" value="free">免费上门</el-radio>
            <el-radio label="fixed" value="fixed">固定上门费</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-col>
      <el-col :span="24" v-if="doorServiceType === 'fixed'">
        <el-form-item label="">
          <div class="acea-row">
            <el-input-number
              :controls="false"
              :min="0"
              :step="1"
              :precision="2"
              :model-value="doorServicePostage"
              @change="onDoorServicePostageChange"
              placeholder="请输入上门服务费金额"
              class="input_width maxW input-number-unit-class"
              class-unit="元"
            />
          </div>
        </el-form-item>
      </el-col>
    </template>
  </el-row>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ name: 'LogisticsSetting' });

const props = defineProps({
  formValidate: {
    type: Object,
    required: true,
  },
  templateList: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['deliveryMethodsChange', 'freightConfigChange', 'addTemp']);

const computedDeliveryMethods = computed({
  get() {
    return props.formValidate.delivery_methods || ['express'];
  },
  set(val) {
    emit('deliveryMethodsChange', val);
  },
});
const hasExpress = computed(() => {
  return props.formValidate.delivery_methods && props.formValidate.delivery_methods.includes('express');
});
const hasDoorService = computed(() => {
  return props.formValidate.delivery_methods && props.formValidate.delivery_methods.includes('door_service');
});
// express 节点读取（新双节点格式）
const expressConfig = computed(() => {
  return (props.formValidate.freight_config && props.formValidate.freight_config.express) || {};
});
const expressType = computed(() => expressConfig.value.type || 'free');
const expressPostage = computed(() => expressConfig.value.postage || 0);
const expressTempId = computed(() => expressConfig.value.temp_id || null);
const computedExpressTempId = computed({
  get() {
    return expressTempId.value;
  },
  set(val) {
    onExpressTempIdChange(val);
  },
});
const computedExpressType = computed({
  get() {
    return expressType.value;
  },
  set(val) {
    onExpressTypeChange(val);
  },
});
// door_service 节点读取
const doorServiceConfig = computed(() => {
  return (props.formValidate.freight_config && props.formValidate.freight_config.door_service) || {};
});
const doorServiceType = computed(() => doorServiceConfig.value.type || 'free');
const doorServicePostage = computed(() => doorServiceConfig.value.postage || 0);
const computedDoorServiceType = computed({
  get() {
    return doorServiceType.value;
  },
  set(val) {
    onDoorServiceTypeChange(val);
  },
});

/** 合并新的运费配置，保留已有的其他节点（切换 express 时保留 door_service，反之亦然） */
function buildConfig(partial) {
  const base =
    props.formValidate.freight_config && typeof props.formValidate.freight_config === 'object'
      ? JSON.parse(JSON.stringify(props.formValidate.freight_config))
      : {};
  return Object.assign({}, base, partial);
}
function onExpressTypeChange(type) {
  // 切换 express type 时只重建 express 节点，保留已有的 door_service
  const express = { type };
  if (type === 'fixed') {
    express.postage = expressPostage.value;
  } else if (type === 'template') {
    express.temp_id = expressTempId.value;
  }
  emit('freightConfigChange', buildConfig({ express }));
}
function onExpressPostageChange(val) {
  const express = { type: 'fixed', postage: parseFloat(val) || 0 };
  emit('freightConfigChange', buildConfig({ express }));
}
function onExpressTempIdChange(val) {
  const express = { type: 'template', temp_id: val || 0 };
  emit('freightConfigChange', buildConfig({ express }));
}
function onDoorServiceTypeChange(type) {
  // 切换 door_service type 时只重建 door_service 节点，保留已有的 express
  const doorService = { type };
  if (type === 'fixed') {
    doorService.postage = doorServicePostage.value;
  }
  emit('freightConfigChange', buildConfig({ door_service: doorService }));
}
function onDoorServicePostageChange(val) {
  const doorService = { type: 'fixed', postage: parseFloat(val) || 0 };
  emit('freightConfigChange', buildConfig({ door_service: doorService }));
}
function addTemp() {
  emit('addTemp');
}
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
