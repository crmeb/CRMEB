<template>
  <div class="schema-editor">
    <Toolbar class="schema-editor__toolbar" :editor="editorRef" :defaultConfig="toolbarConfig" mode="default" />
    <Editor
      class="schema-editor__content"
      v-model="html"
      :defaultConfig="editorConfig"
      mode="default"
      style="height: 360px; overflow-y: hidden"
      @onCreated="handleCreated"
    />
    <!-- AI 写作弹窗（与系统 wangEditor 共用同一组件；默认提示词由后端 Form::editor() 规则下发） -->
    <AiModal
      ref="aiModalRef"
      :ai-default-prompt="aiPrompt"
      :ai-default-system-prompt="aiSystemPrompt"
      :ai-show-system-prompt="aiShowSystemPrompt"
      @insert="insertAiContent"
    />
  </div>
</template>

<script setup>
/**
 * 通用页面富文本编辑器（form-create 自定义组件）
 *
 * 独立基于 wangeditor v5 的精简实现（不经系统 wangEditor 组件的重依赖链），
 * 适配 form-create 的 v-model 契约（modelValue / update:modelValue）；
 * 小图自动转 base64 直插。后端经 Form::editor() 生成 type=SchemaEditor 规则。
 * 工具栏经 registerMenus 引入系统自定义菜单（含「AI」写作按钮），AI 弹窗复用
 * 系统公共组件 AiModal（SSE 流式生成，插入内容以当前内容为基准追加到末尾）。
 */
import '@wangeditor/editor/dist/css/style.css';
import { ref, shallowRef, watch, onMounted, onBeforeUnmount } from 'vue';
import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
// 注册系统自定义菜单（aiMenuKey 等；模块级仅注册一次，重复注册已被内部捕获忽略）
import '@/components/wangEditor/registerMenus';
import AiModal from '@/components/wangEditor/AiModal.vue';
import bus from '@/utils/bus';

// editor-for-vue 未声明 emits 选项，按其内部实际 emit 的事件补声明以消除 dev 告警
if (!Editor.emits) {
  Editor.emits = ['onCreated', 'onChange', 'onDestroyed', 'onMaxLength', 'onFocus', 'onBlur', 'update:modelValue'];
}
if (!Toolbar.emits) {
  Toolbar.emits = ['onCreated'];
}

defineOptions({ name: 'SchemaEditor' });

const props = defineProps({
  /** 富文本 HTML（form-create field 值绑定） */
  modelValue: {
    type: String,
    default: '',
  },
  /** AI 写作默认提示词（后端 Form::editor() ai.prompt 下发；不传时 AiModal 使用内置默认） */
  aiPrompt: {
    type: String,
    default: '',
  },
  /** AI 写作默认系统提示词（后端 Form::editor() ai.system_prompt 下发） */
  aiSystemPrompt: {
    type: String,
    default: '',
  },
  /** 是否显示系统提示词编辑区（后端 Form::editor() ai.show_system_prompt 下发） */
  aiShowSystemPrompt: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

/** 编辑器实例（shallowRef 避免深层代理） */
const editorRef = shallowRef(null);
/** 编辑器内容（v-model 双向绑定） */
const html = ref(props.modelValue || '');
/** AI 写作弹窗实例 */
const aiModalRef = ref(null);

// 外部值变化同步进编辑器（比较旧值防止回写覆盖用户编辑）
watch(
  () => props.modelValue,
  (value) => {
    if (value !== html.value) {
      html.value = value || '';
    }
  },
);

// 编辑内容变化回传 form-create
watch(html, (value) => {
  emit('update:modelValue', value ?? '');
});

/** 工具栏配置：常用排版菜单 + 系统 AI 写作按钮（insertKeys 追加到默认工具栏末尾） */
const toolbarConfig = {
  excludeKeys: ['fullScreen', 'group-video'],
  insertKeys: { index: 32, keys: ['aiMenuKey'] },
};

/** 编辑器配置：小图转 base64 直插（不走服务端上传） */
const editorConfig = {
  placeholder: '',
  autoFocus: false,
  scroll: true,
  MENU_CONF: {
    uploadImage: {
      base64LimitSize: 5 * 1024 * 1024,
    },
  },
};

/**
 * 编辑器创建完成（记录实例供工具栏联动）
 * @param {object} editor 编辑器实例
 * @returns {void}
 */
function handleCreated(editor) {
  editorRef.value = editor;
}

// 总线回调需具名，off 才能正确解绑
function isCurrentEditor(editor) {
  return editor === editorRef.value;
}
// AI 菜单点击（bus 事件，需判断事件来源是当前编辑器实例）
function onAiWriteEvent(editor) {
  if (!isCurrentEditor(editor)) return;
  aiModalRef.value?.open();
}
// AI 生成内容插入编辑器（AiModal 回调；以当前内容为基准追加到末尾）
function insertAiContent(content) {
  const editor = editorRef.value;
  if (!editor || !content) return;
  editor.setHtml(editor.getHtml() + content);
}

// 监听 AI 菜单 bus 事件（组件挂载/卸载期解绑，防止跨页面串扰）
onMounted(() => {
  bus.on('AiWrite', onAiWriteEvent);
});

// 组件卸载前销毁编辑器实例并解绑总线事件
onBeforeUnmount(() => {
  bus.off('AiWrite', onAiWriteEvent);
  editorRef.value?.destroy();
});
</script>

<style lang="scss" scoped>
.schema-editor {
  width: 100%;
  border: 1px solid var(--el-border-color);
  border-radius: 4px;
  z-index: 10;

  &__toolbar {
    border-bottom: 1px solid var(--el-border-color);
  }

  &__content {
    min-height: 360px;
  }
}
</style>
