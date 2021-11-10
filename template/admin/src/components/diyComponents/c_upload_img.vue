<template>
  <div class="upload_img">
    <div class="title">{{ datas[name].title }}</div>
    <div class="box" @click="modalPicTap('单选')">
      <img :src="datas[name].url" alt="" v-if="datas[name].url" />
      <div class="upload-box" v-else>
        <Icon type="ios-camera-outline" size="36" />
      </div>
    </div>
    <div>
      <Modal
        v-model="modalPic"
        width="950px"
        scrollable
        footer-hide
        closable
        title="上传商品图"
        :mask-closable="false"
        :z-index="888"
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
  computed: {},
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
      datas: this.configData[this.configNum],
    };
  },
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
      },
      deep: true,
    },
  },
  mounted() {
    console.log("ssss", this.configNum);
  },
  methods: {
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
        this.configData[this.configNum][this.name].url = pc.att_dir;
        this.modalPic = false;
      });
    },
  },
};
</script>

<style scoped lang="stylus">
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
  background-color: #f2f2f2;

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
