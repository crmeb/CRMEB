<template>
  <el-drawer
    v-model="visible"
    :title="title"
    direction="rtl"
    size="720"
    :wrapper-closable="false"
    custom-class="schema-drawer"
    @closed="emit('closed')"
  >
    <div class="schema-drawer__content">
      <slot />
    </div>
    <template #footer>
      <div v-if="showFooter" class="schema-drawer__footer">
        <slot name="footer">
          <el-button v-db-click @click="visible = false">取消</el-button>
          <el-button type="primary" :loading="submitting" v-db-click @click="emit('submit')">确定</el-button>
        </slot>
      </div>
    </template>
  </el-drawer>
</template>

<script setup>
/**
 * 通用页面右侧抽屉壳
 *
 * 统一承载新增/编辑表单抽屉与详情抽屉：默认页脚为"取消/确定"（表单模式），
 * 详情模式可通过 footer 插槽覆盖为空。
 */
import { computed } from 'vue';

defineOptions({ name: 'SchemaDrawer' });

const props = defineProps({
  /** 是否显示 */
  modelValue: {
    type: Boolean,
    default: false,
  },
  /** 抽屉标题 */
  title: {
    type: String,
    default: '',
  },
  /** 确定按钮加载状态 */
  submitting: {
    type: Boolean,
    default: false,
  },
  /** 是否显示默认页脚（详情模式传 false） */
  showFooter: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:modelValue', 'submit', 'closed']);

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});
</script>

<style lang="scss" scoped>
.schema-drawer__content {
  padding-bottom: 16px;
}
.schema-drawer__footer {
  display: flex;
  justify-content: flex-end;
}
</style>
