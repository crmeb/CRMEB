<template>
  <div class="hot_imgs">
    <div class="title">
      {{ datas[name].title }}
    </div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="datas[name].list"
        group="peoples"
        handle=".move-icon"
      >
        <div
          class="item"
          v-for="(item, index) in datas[name].list"
          :key="index"
        >
          <div class="move-icon">
            <span class="iconfont icondrag2"></span>
          </div>
          <div class="img-box" @click="modalPicTap('单选', index)">
            <img :src="item.img" alt="" v-if="item.img" />
            <div class="upload-box" v-else>
              <Icon type="ios-camera-outline" size="36" />
            </div>
            <div
              v-if="!datas[name].isDelete"
              class="delect-btn"
              @click.stop="bindDelete(item, index)"
            >
              <Icon type="md-close-circle" size="26" />
            </div>
          </div>
          <div class="info">
            <div class="info-item" v-for="(infos, key) in item.info" :key="key">
              <div class="info-item" v-if="infos.title === '链接'">
                <span>{{ infos.title }}</span>
                <div class="input-box" @click="getLink(index, key)">
                  <Input
                    v-model="infos.value"
                    :placeholder="infos.tips"
                    :maxlength="infos.maxlength"
                    icon="ios-arrow-forward"
                    readonly
                  />
                </div>
              </div>
              <div v-else class="info-item">
                <span>{{ infos.title }}</span>
                <div class="input-box">
                  <Input
                    v-model="infos.value"
                    :placeholder="infos.tips"
                    :maxlength="infos.maxlength"
                    width="250px"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </draggable>
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
    <template v-if="datas[name]">
      <div
        class="add-btn"
        v-if="
          datas[name].list.length < datas[name].max || datas[name].max == ''
        "
      >
        <Button
          type="primary"
          ghost
          style="
            width: 100%;
            height: 40px;
            border-color: #1890ff;
            color: #1890ff;
          "
          @click="addBox"
          >添加图片
        </Button>
      </div>
    </template>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import linkaddress from "@/components/linkaddress";

import uploadPictures from "@/components/uploadPictures";
import vuedraggable from "vuedraggable";
export default {
  name: "c_upload_list",
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
    moduleName: {
      type: String,
    },
  },
  components: {
    linkaddress,
    uploadPictures,
    draggable: vuedraggable,
  },
  data() {
    return {
      defaults: {},
      menus: [],
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
      keyIndex: 0,
      datas: this.configData[this.configNum],
    };
  },
  mounted() {},
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
      },
      deep: true,
    },
  },
  methods: {
    getLink(index, key) {
      this.activeIndex = index;
      this.keyIndex = key;
      this.$refs.linkaddres.modals = true;
    },
    linkUrl(e) {
      console.log(
        this.datas[this.name].list[this.activeIndex].info[this.keyIndex].value
      );
      console.log(e);
      this.datas[this.name].list[this.activeIndex].info[this.keyIndex].value =
        e;
    },
    addBox() {
      let val = {
        img: "",
        info: [
          {
            maxlength: 10,
            tips: "选填，不超过十个字",
            title: "标题",
            value: "",
          },
          {
            maxlength: 999,
            tips: "请填写链接",
            title: "链接",
            value: "",
          },
        ],
      };
      console.log(this.moduleName);
      if (this.moduleName == "swiperBg" || this.moduleName == "menus") {
        this.configData[this.configNum][this.name].list.push(val);
        return;
      }
      if (this.configData[this.configNum][this.name].list.length == 0) {
        let uploadListData =
          this.$store.state.userInfo["uploadListData" + this.moduleName];
        this.configData[this.configNum][this.name].list.push(uploadListData);
      } else {
        let obj = JSON.parse(
          JSON.stringify(
            this.configData[this.configNum][this.name].list[
              this.configData[this.configNum][this.name].list.length - 1
            ]
          )
        );
        this.configData[this.configNum][this.name].list.push(obj);
      }
    },
    // 点击图文封面
    modalPicTap(title, index) {
      this.activeIndex = index;
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
        this.configData[this.configNum][this.name].list[this.activeIndex].img =
          pc.att_dir;
        this.modalPic = false;
      });
    },
    // 删除
    bindDelete(item, index) {
      if (this.configData[this.configNum][this.name].list.length == 1) {
        let lastObj = this.configData[this.configNum][this.name].list[0];
        this.$store.commit("userInfo/uploadList" + this.moduleName, lastObj);
      }
      this.configData[this.configNum][this.name].list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="stylus">
.icondrag2 {
  font-size: 26px;
  color: #d8d8d8;
}

.hot_imgs {
  margin-bottom: 20px;

  .title {
    padding: 0 0 13px 0;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .list-box {
    .item {
      position: relative;
      display: flex;
      margin-top: 20px;

      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 80px;
        cursor: move;
        color: #D8D8D8;
      }

      .img-box {
        position: relative;
        width: 80px;
        height: 80px;

        img {
          width: 100%;
          height: 100%;
        }
      }

      .info {
        flex: 1;
        margin-left: 22px;

        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;
          width: 300px;

          span {
            width: 40px;
            font-size: 13px;
          }

          .input-box {
            flex: 1;
          }
        }
      }

      .delect-btn {
        position: absolute;
        right: -12px;
        top: -12px;
        color: #999999;

        .iconfont {
          font-size: 28px;
          color: #999;
        }
      }
    }
  }

  .add-btn {
    margin-top: 10px;
  }
}

.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: #ccc;
}

.iconfont {
  color: #DDDDDD;
  font-size: 28px;
}
</style>
