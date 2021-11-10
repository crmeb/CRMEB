<template>
  <div class="upload_img" v-if="configData">
    <div class="header">{{ configData.header }}</div>
    <div class="title">{{ configData.title }}</div>
    <div class="box" @click="modalPicTap('单选')">
      <img :src="configData.url" alt="" v-if="configData.url" />
      <div class="upload-box" v-else>
        <Icon type="ios-camera-outline" size="36" />
      </div>
      <span
        class="iconfont-diy icondel_1"
        @click.stop="bindDelete"
        v-if="configData.url && configData.type"
      ></span>
    </div>
    <div>
      <Modal
        v-model="modalPic"
        width="950px"
        scrollable
        footer-hide
        closable
        :title="configData.header ? configData.header : '上传图片'"
        :mask-closable="false"
        :z-index="1"
      >
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </Modal>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import uploadPictures from "@/components/uploadPictures";
export default {
  name: "c_upload_img",
  components: {
    uploadPictures,
  },
  computed: {
    ...mapState({
      tabVal: (state) => state.admin.mobildConfig.searchConfig.data.tabVal,
    }),
  },
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaultList: [
        {
          name: "a42bdcc1178e62b4694c830f028db5c0",
          url: "https://o5wwk8baw.qnssl.com/a42bdcc1178e62b4694c830f028db5c0/avatar",
        },
        {
          name: "bc7521e033abdd1e92222d733590f104",
          url: "https://o5wwk8baw.qnssl.com/bc7521e033abdd1e92222d733590f104/avatar",
        },
      ],
      defaults: {},
      configData: {},
      modalPic: false,
      isChoice: "单选",
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      activeIndex: 0,
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
  },
  methods: {
    bindDelete() {
      this.configData.url = "";
    },
    // 点击图文封面
    modalPicTap(title) {
      this.modalPic = true;
    },
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        "test-dialog",
        function (editor, uiName) {
          let dialog = new window.UE.ui.Dialog({
            iframeUrl: "/admin/widget.images/index.html?fodder=dialog",
            editor: editor,
            name: uiName,
            title: "上传图片",
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
        37
      );
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        this.configData.url = pc.att_dir;
        this.modalPic = false;
      });
    },
  },
};
</script>

<style scoped lang="stylus">
.header {
  font-size: 14px;
  color: #000;
}

.title {
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  font-size: 12px;
  color: #999;
}

.box {
  width: 60px;
  height: 60px;
  margin-bottom: 10px;
  position: relative;

  .iconfont-diy {
    position: absolute;
    top: -15px;
    right: -8px;
    font-size: 25px;
    color: #999;
  }

  img {
    width: 100%;
    height: 100%;
  }
}

.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background: #ccc;
}
</style>
