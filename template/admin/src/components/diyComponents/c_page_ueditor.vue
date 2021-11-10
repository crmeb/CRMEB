<template>
  <div class="box" v-if="datas">
    <vue-ueditor-wrap
      v-model="datas.val"
      @beforeInit="addCustomDialog"
      :config="myConfig"
      style="width: 100%; height: 60%"
    ></vue-ueditor-wrap>
  </div>
</template>

<script>
import VueUeditorWrap from "vue-ueditor-wrap";
export default {
  name: "c_page_ueditor",
  props: {
    name: {
      type: String,
    },
    configData: {
      type: null,
    },
    configNum: {
      type: Number | String,
      default: "default",
    },
  },
  components: { VueUeditorWrap },
  data() {
    return {
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 350, // 初始容器高度
        initialFrameWidth: "100%", // 初始容器宽度
        UEDITOR_HOME_URL: "/admin/UEditor/",
        serverUrl: "",
      },
      datas: {},
    };
  },
  created() {
    this.datas = this.configData[this.configNum][this.name];
  },
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum][this.name];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        "test-dialog",
        function (editor, uiName) {
          // 创建 dialog
          let dialog = new window.UE.ui.Dialog({
            iframeUrl: "/admin/widget.images/index.html?fodder=dialog",
            editor: editor,
            name: uiName,
            title: "上传图片",
            cssRules: "width:960px;height:550px;padding:20px;",
          });
          this.dialog = dialog;
          let btn = new window.UE.ui.Button({
            name: "dialog-button",
            title: "上传图片",
            cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;`,
            onclick: function () {
              // 渲染dialog
              dialog.render();
              dialog.open();
            },
          });
          return btn;
        },
        37
      );
      window.UE.registerUI(
        "video-dialog",
        function (editor, uiName) {
          let dialog = new window.UE.ui.Dialog({
            iframeUrl: "/admin/widget.video/index.html?fodder=video",
            editor: editor,
            name: uiName,
            title: "上传视频",
            cssRules: "width:1000px;height:500px;padding:20px;",
          });
          this.dialog = dialog;
          let btn = new window.UE.ui.Button({
            name: "video-button",
            title: "上传视频",
            cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -320px -20px;`,
            onclick: function () {
              // 渲染dialog
              dialog.render();
              dialog.open();
            },
          });
          return btn;
        },
        38
      );
    },
  },
};
</script>

<style scoped>
</style>