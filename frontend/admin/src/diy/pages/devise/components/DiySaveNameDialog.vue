<template>
  <el-dialog
    :model-value="modelValue"
    width="470px"
    title="设置模板名称"
    :show-close="true"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <el-input :model-value="saveName" placeholder="请输入模板名称" @update:model-value="$emit('update:saveName', $event)"></el-input>
    <template #footer>
      <span class="dialog-footer">
        <el-button v-db-click @click="$emit('update:modelValue', false)">取 消</el-button>
        <el-button type="primary" v-db-click @click="$emit('confirm')">确 定</el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  saveName: { type: String, default: '' },
});

defineEmits(['update:modelValue', 'update:saveName', 'confirm']);
</script>
