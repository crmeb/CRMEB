<template>
  <el-dialog v-model="modals_son" :title="title" :close-on-click-modal="false" width="900px">
    <el-button type="primary" id="savefile" class="mr5 mb15" v-db-click @click="savefile">保存</el-button>
    <el-button id="undo" class="mr5 mb15" v-db-click @click="undofile">撤销</el-button>
    <el-button id="redo" class="mr5 mb15" v-db-click @click="redofile">回退</el-button>
    <el-button id="refresh" class="mb15" v-db-click @click="refreshfile">刷新</el-button>
    <textarea ref="mycode" class="codesql public_text" v-model="codeProxy" style="height: 80vh"></textarea>
  </el-dialog>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { savefileApi } from '../api';
import { EditorView, basicSetup } from 'codemirror';
import { undo, redo } from '@codemirror/commands';
import { getCookies } from '@/libs/util';

defineOptions({ name: 'opendir' });

const props = defineProps({
  rows: {
    type: Object,
    default: () => ({}),
  },
  code: {
    type: String,
    default: ' ',
  },
  modals: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:code']);

const mycode = ref(null);
let editor = null; // CodeMirror v6 EditorView 实例（非响应式）
const isShowLogn = ref(false); // 登录
const isShowList = ref(false); // 登录之后列表
const spinShow = ref(false);
const loading = ref(false);

const formItem = ref({
  dir: '',
  superior: 0,
  filedir: '',
});
const pathname = ref('');
const modals_son = ref(props.modals);
const fileToken = getCookies('file_token');

// Vue3 中 prop 不可直接 v-model，用可写 computed 代理
const codeProxy = computed({
  get: () => props.code,
  set: (val) => emit('update:code', val),
});

watch(
  () => props.code,
  (newValue, oldValue) => {
    if (editor) setEditorValue(newValue);
  },
  { deep: true }, // 默认值是 false，代表是否深度监听
);
watch(
  () => props.modals,
  (newValue, oldValue) => {
    modals_son.value = newValue;
  },
  { deep: true }, // 默认值是 false，代表是否深度监听
);

onMounted(() => {
  editor = new EditorView({
    doc: props.code,
    extensions: [basicSetup],
    parent: mycode.value,
  });
});

function getEditorValue() {
  return editor ? editor.state.doc.toString() : '';
}
function setEditorValue(value) {
  editor.dispatch({ changes: { from: 0, to: editor.state.doc.length, insert: value } });
}

// 保存
function savefile() {
  let data = {
    comment: getEditorValue(),
    filepath: pathname.value,
    fileToken: fileToken,
  };
  savefileApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modals_son.value = false;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 撤销
function undofile() {
  if (editor) undo(editor);
}
function redofile() {
  if (editor) redo(editor);
}
// 刷新（CodeMirror v6 自动布局，无需手动 refresh，保留空实现）
function refreshfile() {}
</script>
<style lang="scss" scoped>
:deep(.cm-editor) {
  height: 70vh !important;
}
</style>
