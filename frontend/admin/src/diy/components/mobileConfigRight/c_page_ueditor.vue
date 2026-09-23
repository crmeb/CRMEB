<template>
  <div class="box" v-if="configData">
    <WangEditor :content="val" @editorContent="getEditorContent" style="width: 100%; height: 60%"></WangEditor>
  </div>
</template>

<script setup>
import iconsPng from '@/assets/images/icons.png';
import { ref, watch, onBeforeUnmount } from 'vue';
import WangEditor from '@/components/wangEditor/index.vue';

defineOptions({ name: 'c_page_ueditor' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const myConfig = ref({
  autoHeightEnabled: false, // 编辑器不自动被内容撑高
  initialFrameHeight: 350, // 初始容器高度
  initialFrameWidth: '100%', // 初始容器宽度
  UEDITOR_HOME_URL: '/admin/UEditor/',
  serverUrl: '',
});
const description = ref('');
const defaults = ref({});
const configData = ref(undefined);
const val = ref('');

watch(
  () => props.configObj,
  (nVal, oVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    if (oVal && nVal.timestamp != oVal.timestamp) {
      val.value = configData.value.val;
    }
  },
  { immediate: true, deep: true },
);

onBeforeUnmount(() => {
  val.value = '';
});

function getEditorContent(data) {
  configData.value.val = data;
}
// 添加自定义弹窗
function addCustomDialog(editorId) {
  window.UE.registerUI(
    'test-dialog',
    function (editor, uiName) {
      // 创建 dialog
      let dialog = new window.UE.ui.Dialog({
        iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
        editor: editor,
        name: uiName,
        title: '上传图片',
        cssRules: 'width:1200px;height:500px;padding:20px;',
      });
      let btn = new window.UE.ui.Button({
        name: 'dialog-button',
        title: '上传图片',
        cssRules: `background-image: url(${iconsPng});background-position: -726px -77px;`,
        onclick: function () {
          // 渲染dialog
          dialog.render();
          dialog.open();
        },
      });
      return btn;
    },
    37,
  );
  window.UE.registerUI(
    'video-dialog',
    function (editor, uiName) {
      let dialog = new window.UE.ui.Dialog({
        iframeUrl: '/admin/widget.video/index.html?fodder=video',
        editor: editor,
        name: uiName,
        title: '上传视频',
        cssRules: 'width:1000px;height:500px;padding:20px;',
      });
      let btn = new window.UE.ui.Button({
        name: 'video-button',
        title: '上传视频',
        cssRules: `background-image: url(${iconsPng});background-position: -320px -20px;`,
        onclick: function () {
          // 渲染dialog
          dialog.render();
          dialog.open();
        },
      });
      return btn;
    },
    38,
  );
}
</script>

<style scoped lang="scss">
.box {
  padding: 0 15px;
}
</style>
