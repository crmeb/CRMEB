<template>
  <div>
    <div v-show="!monacoBox">
      <div class="wang-editor">
        <Toolbar class="editor-toolbar" :editor="editorRef" :defaultConfig="toolbarConfig" mode="default" />
        <Editor
          class="editor-content"
          v-model="valueHtml"
          :defaultConfig="editorConfig"
          mode="default"
          style="height: 600px; overflow-y: hidden"
          @onCreated="handleCreated"
          @onChange="handleChange"
          @onDestroyed="handleDestroyed"
        />
      </div>
    </div>
    <div v-if="monacoBox">
      <el-button type="primary" class="bottom" v-db-click @click="getHtmlint">可视化界面</el-button>
      <monaco class="monaco-box" @change="changeValue" :value="newHtml" />
    </div>

    <el-dialog v-model="modalPic" width="1024px" title="上传图片" :close-on-click-modal="false">
      <uploadPictures v-if="modalPic" :isChoice="isChoice" @getPic="getPic"></uploadPictures>
    </el-dialog>
    <el-dialog v-model="modalVideo" width="1024px" title="上传视频" :close-on-click-modal="false">
      <uploadVideo v-if="modalVideo" @getVideo="getvideo"></uploadVideo>
    </el-dialog>

    <!-- AI 写作弹窗（公共组件，供 SchemaEditor 复用） -->
    <AiModal
      ref="aiModalRef"
      :ai-default-prompt="aiDefaultPrompt"
      :ai-default-system-prompt="aiDefaultSystemPrompt"
      :ai-show-system-prompt="aiShowSystemPrompt"
      @insert="insertAiContent"
    />
  </div>
</template>
<script setup>
import '@wangeditor/editor/dist/css/style.css';
import { nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue';
import { getCurrentInstance } from 'vue';
import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
// editor-for-vue 未声明 emits 选项；编辑器 destroyed 事件在组件卸载后异步触发，
// dev 下会告警 "emitted event onDestroyed ... neither declared in the emits option"，
// 按其内部实际 emit 的事件补声明以消除警告
if (!Editor.emits) {
  Editor.emits = [
    'onCreated',
    'onChange',
    'onDestroyed',
    'onMaxLength',
    'onFocus',
    'onBlur',
    'customAlert',
    'customPaste',
  ];
}
import monaco from './monaco';
import AiModal from './AiModal.vue';
import './registerMenus';
import uploadPictures from '@/components/uploadPictures';
import uploadVideo from '@/components/uploadVideo2';

import util from '@/utils/bus';

defineOptions({ name: 'WangEditor' });

const props = defineProps({
  content: {
    type: String,
    default: '',
  },
  // AI 写作默认问题（写作提示词输入框默认值），不传时使用组件内置默认
  aiDefaultPrompt: {
    type: String,
    default: '',
  },
  // AI 写作默认系统提示词，不传时使用组件内置默认
  aiDefaultSystemPrompt: {
    type: String,
    default: '',
  },
  // 是否显示系统提示词编辑区（默认隐藏，隐藏时仍按默认值生效；页面传 true 显示）
  aiShowSystemPrompt: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['editorContent', 'input']);

const { proxy } = getCurrentInstance();

// AI 写作默认系统提示词（生成时按行拆分后作为 prompts 传给后端）
const AI_DEFAULT_SYSTEM_PROMPT =
  '你是一名专业的新媒体内容编辑，擅长撰写图文文章。请根据用户要求直接撰写文章正文。\n' +
  '输出要求：使用 Markdown 格式，章节标题用 ## 或 ###，重点内容用 **加粗**，条目用 - 列表；直接输出正文，不要输出任何解释说明或与正文无关的内容。';

// AI 写作输入框默认提示词（打开弹窗时输入框为空则填充，用户可修改）
const AI_DEFAULT_PROMPT = '写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。';

const editorRef = shallowRef(null);
const valueHtml = ref('<p><br></p>');
const newHtml = ref('');
const monacoBox = ref(false);
const modalPic = ref(false);
const isChoice = ref('多选');
const modalVideo = ref(false);
const aiModalRef = ref(null);

// v4 菜单键 -> v5 菜单键对照（head->headerSelect、foreColor->color、backColor->bgColor、
// fontName->fontFamily、indent->group-indent、list->bulletedList/numberedList、
// justify->group-justify、quote->blockquote、emoticon->emotion、code->codeBlock、splitLine->divider）
const toolbarConfig = {
  toolbarKeys: [
    'alertHtml',
    'headerSelect',
    'bold',
    'fontSize',
    'fontFamily',
    'italic',
    'underline',
    'through', // 删除线（v5 的 key，v4 叫 strikeThrough）
    'indent', // 首行缩进（v5 手写 toolbarKeys 引用 group-indent 会因菜单表无此 key 而中断渲染，故用其子菜单平铺）
    'delIndent',
    'lineHeight',
    'color',
    'bgColor',
    'insertLink',
    'bulletedList',
    'numberedList',
    'justifyLeft',
    'justifyRight',
    'justifyCenter',
    'justifyJustify', // 对齐方式（同上，平铺替代 group-justify）
    'blockquote',
    'emotion',
    'uploadImgMenuKey', // 图片：打开项目素材库选择组件（替代 v5 内置 base64 直插）
    'alertMenuKey',
    'codeBlock',
    'divider',
    'aiMenuKey',
  ],
};

// 注意：onCreated/onChange 等回调不能写进 defaultConfig（vue 包装组件会抛错），需用组件事件
const editorConfig = {
  placeholder: '',
  readOnly: false,
  autoFocus: false,
  scroll: true,
  MENU_CONF: {
    // 对应 v4 的 uploadImgShowBase64：小图转 base64 直插，不走服务端
    uploadImage: {
      base64LimitSize: 5 * 1024 * 1024,
    },
  },
};

// 外部传入内容变化时同步进编辑器（比较旧值防止回写覆盖用户编辑）
watch(
  () => props.content,
  (val) => {
    if (val && editorRef.value && val !== editorRef.value.getHtml()) {
      valueHtml.value = val;
      newHtml.value = val;
    }
  },
);

// 总线回调需具名，off 才能正确解绑
function isCurrentEditor(editor) {
  return editor === editorRef.value;
}
function onVideoEvent(editor) {
  if (!isCurrentEditor(editor)) return;
  getvideoint();
}
function onUploadImgEvent(editor) {
  if (!isCurrentEditor(editor)) return;
  getimg();
}
function onHtmlEvent(editor) {
  if (!isCurrentEditor(editor)) return;
  getHtmlint();
}
function onAiWriteEvent(editor) {
  if (!isCurrentEditor(editor)) return;
  // AI 写作弹窗为公共组件（SchemaEditor 复用），提示词默认值由其内部维护
  aiModalRef.value?.open();
}

onMounted(() => {
  if (props.content) {
    valueHtml.value = props.content;
    newHtml.value = props.content;
  }
  util.on('Video', onVideoEvent);
  util.on('UploadImg', onUploadImgEvent);
  util.on('Html', onHtmlEvent);
  util.on('AiWrite', onAiWriteEvent);
});

onBeforeUnmount(() => {
  util.off('Video', onVideoEvent);
  util.off('UploadImg', onUploadImgEvent);
  util.off('Html', onHtmlEvent);
  util.off('AiWrite', onAiWriteEvent);
  const editor = editorRef.value;
  if (editor) editor.destroy();
});

function handleCreated(editor) {
  editorRef.value = editor;
  if (props.content && props.content !== editor.getHtml()) {
    editor.setHtml(props.content);
    newHtml.value = props.content;
  }
}
// editor-for-vue 未声明 emits，onDestroyed 无监听时会触发 Vue 未声明事件警告；顺带清理实例引用
function handleDestroyed() {
  editorRef.value = null;
}
function handleChange(editor) {
  newHtml.value = editor.getHtml();
  emit('editorContent', newHtml.value);
}
// 源码视图（monaco）内容回传
function changeValue(value) {
  newHtml.value = value;
  emit('editorContent', value);
  emit('input', value);
}
// 获取多张图信息
function getPic(pc) {
  modalPic.value = false;
  insertHtmlToEditor(pc.map((item) => `<img src="${item.att_dir}" style="max-width:100%;"/>`).join(''));
}
function getimg() {
  modalPic.value = true;
  isChoice.value = '多选';
}
function getvideoint() {
  proxy.$videoModal((e) => {
    modalVideo.value = false;
    insertHtmlToEditor(createVideoHtml(e));
  });
}
function insertHtmlToEditor(html) {
  const editor = editorRef.value;
  if (!editor || !html) return;
  nextTick(() => {
    if (editor.isDestroyed) return;
    editor.restoreSelection();
    editor.dangerouslyInsertHtml(html);
  });
}
function createVideoHtml(src) {
  if (!src) return '';
  const safeSrc = String(src)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
  return `<div data-w-e-type="video" data-w-e-is-void><video poster="" controls="true" width="auto" height="auto"><source src="${safeSrc}" type="video/mp4"/></video></div>`;
}
// 可视化 <-> 源码视图切换
function getHtmlint() {
  monacoBox.value = !monacoBox.value;
  if (!monacoBox.value) {
    // 切回可视化：把源码视图（或最新内容）写回编辑器
    if (editorRef.value && newHtml.value && newHtml.value !== editorRef.value.getHtml()) {
      editorRef.value.setHtml(newHtml.value);
    }
  }
}
function getvideo(data) {
  modalVideo.value = false;
  insertHtmlToEditor(createVideoHtml(data));
}

// AI 生成内容插入编辑器（AiModal 回调；以当前内容为基准追加到末尾）
function insertAiContent(html) {
  // 源码模式下先切回可视化模式，确保内容写入可视化编辑器
  if (monacoBox.value) getHtmlint();
  if (editorRef.value && html) {
    editorRef.value.setHtml(editorRef.value.getHtml() + html);
  }
}
</script>

<style lang="scss" scoped>
.bottom {
  margin-bottom: 10px;
  cursor: pointer;
}
.monaco-box :deep(.el-textarea__inner) {
  height: 600px;
}
:deep(.w-e-toolbar) {
  z-index: 2 !important;
}
.wang-editor {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
  background-color: #fff;
}
.editor-toolbar {
  border-bottom: 1px solid #dcdfe6;
}
</style>
