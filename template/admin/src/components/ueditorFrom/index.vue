<template>
  <div>
    <vue-ueditor-wrap
      @beforeInit="addCustomDialog"
      v-model="contents"
      :config="myConfig"
      style="width: 90%"
    ></vue-ueditor-wrap>
  </div>
</template>

<script>
import VueUeditorWrap from "vue-ueditor-wrap";
export default {
  name: "index",
  components: { VueUeditorWrap },
  props: {
    content: "",
  },
  watch: {
    content: {
      handler(val) {
        // this.contents = val
        // this.$emit('getContent', val);
      },
      deep: true,
    },
  },
  data() {
    return {
      contents: this.content,
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 200, // 初始容器高度
        initialFrameWidth: "100%", // 初始容器宽度
        // UEditor 资源文件的存放路径，如果你使用的是 vue-cli 生成的项目，通常不需要设置该选项，vue-ueditor-wrap
        // 会自动处理常见的情况，如果需要特殊配置，参考下方的常见问题2
        UEDITOR_HOME_URL: "/admin/UEditor/",
        // 上传文件接口（这个地址是我为了方便各位体验文件上传功能搭建的临时接口，请勿在生产环境使用！！！）
        serverUrl: "",
      },
    };
  },
  methods: {
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        "test-dialog",
        function (editor, uiName) {
          // 创建 dialog
          let dialog = new window.UE.ui.Dialog({
            // 指定弹出层中页面的路径，这里只能支持页面，路径参考常见问题 2
            iframeUrl: "/admin/widget.images/index.html?fodder=dialog",
            // 需要指定当前的编辑器实例
            editor: editor,
            // 指定 dialog 的名字
            name: uiName,
            // dialog 的标题
            title: "上传图片",
            // 指定 dialog 的外围样式
            cssRules: "width:960px;height:550px;padding:20px;",
          });
          this.dialog = dialog;
          // 参考上面的自定义按钮
          var btn = new window.UE.ui.Button({
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
        37 /* 指定添加到工具栏上的那个位置，默认时追加到最后 */,
        editorId /* 指定这个UI是哪个编辑器实例上的，默认是页面上所有的编辑器都会添加这个按钮 */
      );
    },
  },
  created() {},
};
</script>

<style scoped>
</style>
