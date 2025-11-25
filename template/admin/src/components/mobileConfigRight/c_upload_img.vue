<template>
  <div class="upload_img" :class="configData.type == 'code' ? 'on' : ''" v-if="configData">
    <div class="header">{{ configData.header }}</div>
    <div class="title">{{ configData.title }}</div>
    <div class="list">
      <div class="item">
        <div class="name">{{ configData.type == 'code' ? configData.name : '图片' }}</div>
        <div class="picTxt">
          <div class="box" @click="modalPicTap('单选')">
            <div class="pictrue acea-row row-center-wrapper" v-if="configData.url">
              <video v-if="configData.video" :src="configData.url"></video>
              <img :src="configData.url" alt="" v-else />
              <div v-if="configData.delType" class="iconfont icondel_1" @click.stop="bindDelete"></div>
            </div>
            <div class="upload-box" v-else><i class="el-icon-plus"></i></div>
          </div>
          <div class="tip">{{ configData.info }}</div>
        </div>
      </div>
      <div
        v-if="configData.type != 'code' && defaults.name == 'customerService' && defaults.buttonConfig.tabVal == 0"
        class="item"
      >
        <div class="name">链接</div>
        <el-input v-model="configData.link" placeholder="输入链接">
          <i class="el-icon-link" slot="suffix" @click="getLink" />
        </el-input>
      </div>
    </div>
    <div>
      <el-dialog :visible.sync="modalPic" width="960px" :title="configData.name ? configData.name : '上传图片'">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          :isType="configData.video ? 2 : 1"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
    </div>
    <linkaddress v-if="configData.type != 'code'" ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import linkaddress from '@/components/linkaddress';
import uploadPictures from '@/components/uploadPictures';
export default {
  name: 'c_upload_img',
  components: {
    uploadPictures,
    linkaddress,
  },
  computed: {
    ...mapState({
      tabVal: (state) => state.mobildConfig.searchConfig.data.tabVal,
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
          name: 'a42bdcc1178e62b4694c830f028db5c0',
          url: 'https://o5wwk8baw.qnssl.com/a42bdcc1178e62b4694c830f028db5c0/avatar',
        },
        {
          name: 'bc7521e033abdd1e92222d733590f104',
          url: 'https://o5wwk8baw.qnssl.com/bc7521e033abdd1e92222d733590f104/avatar',
        },
      ],
      defaults: {},
      configData: {},
      modalPic: false,
      isChoice: '单选',
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
    linkUrl(e) {
      this.configData.link = e;
    },
    getLink() {
      this.$refs.linkaddres.modals = true;
    },
    bindDelete() {
      this.configData.url = '';
    },
    // 点击图文封面
    modalPicTap(title) {
      console.log(this.configData);
      if (this.configData.video == 1) {
        this.$videoModal((e) => {
          this.configData.url = e;
        });
      } else {
        this.modalPic = true;
      }
    },
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        'test-dialog',
        function (editor, uiName) {
          let dialog = new window.UE.ui.Dialog({
            iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
            editor: editor,
            name: uiName,
            title: '上传图片',
            cssRules: 'width:1200px;height:500px;padding:20px;',
          });
          this.dialog = dialog;
          // 参考上面的自定义按钮
          var btn = new window.UE.ui.Button({
            name: 'dialog-button',
            title: '上传图片',
            cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;`,
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

<style scoped lang="scss">
.upload_img {
  padding: 0 15px;
  &.on {
    .title {
      margin-bottom: 0;
      padding-bottom: 0;
    }
    .list {
      padding: 0;
      height: auto;
      background: #fff;
      padding-bottom: 20px;
      .box {
        margin-bottom: 0;
      }
      .name {
        width: 75px;
      }
    }
  }
  .list {
    width: 370px;
    background: #f9f9f9;
    border-radius: 3px;
    padding: 16px 20px;
  }
  .item {
    display: flex;
    align-items: center;
    .name {
      font-size: 12px;
      color: #999999;
      margin-right: 16px;
      white-space: nowrap;
    }
    ::v-deep.ivu-input-icon {
      color: #bbbbbb;
    }
    .picTxt {
      display: flex;
      align-items: center;
      .tip {
        color: #bbbbbb;
        font-size: 12px;
        margin-left: 20px;
      }
    }
  }
}
.header {
  font-size: 14px;
  color: #000;
}
.title {
  margin-bottom: 10px;
  padding-bottom: 10px;
  font-size: 12px;
  color: #999;
}
.box {
  width: 64px;
  height: 64px;
  margin-bottom: 10px;
  position: relative;
  background: url(../../assets/images/transparents.jpg) no-repeat;
  background-size: 100% 100%;
  border-radius: 3px;
  .pictrue {
    position: relative;
    width: 100%;
    height: 100%;
    video {
      width: 100%;
      height: 100%;
    }
    .iconfont {
      position: absolute;
      right: -12px;
      top: -19px;
      font-size: 24px;
      color: #cccccc;
    }
  }
  img {
    width: 64px;
    border-radius: 3px;
    max-height: 64px;
    object-fit: cover;
  }
}

.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #eeeeee;
  .ivu-icon {
    color: #ccc;
  }
}
</style>
