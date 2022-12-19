<template>
  <div class="hot_imgs">
    <div class="list-box">
      <draggable class="dragArea list-group" :list="listData" group="peoples" handle=".move-icon">
        <div class="item" v-for="(item, index) in listData" :key="index">
          <div class="move-icon">
            <span class="iconfont-diy icondrag"></span>
          </div>
          <div class="img-box" @click="modalPicTap('单选', index)">
            <img :src="item.pic" alt="" v-if="item.pic && item.pic != ''" />
            <div class="upload-box" v-else>
              <Icon type="ios-camera-outline" size="36" />
            </div>
          </div>
          <div class="info">
            <div class="info-item" v-if="item.hasOwnProperty('name')">
              <span>{{ type == 1 ? '管理名称：' : type == 2 ? '广告名称' : '服务名称：' }}</span>
              <div class="input-box">
                <Input v-model="item.name" :placeholder="type == 2 ? '请输入名称' : '服务中心'" :maxlength="4" />
              </div>
            </div>
            <div class="info-item">
              <span>链接地址：</span>
              <div class="input-box" @click="getLink(index)">
                <Input v-model="item.url" icon="ios-arrow-forward" readonly placeholder="选择链接" />
              </div>
            </div>
          </div>
          <div v-if="type != 1" class="delect-btn" @click.stop="bindDelete(item, index)">
            <span class="iconfont-diy icondel_1"></span>
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
    <template v-if="listData">
      <div class="add-btn" v-if="(type != 1 && type != 2) || (type == 2 && listData.length < 5)">
        <Button
          type="primary"
          ghost
          style="width: 100px; height: 30px; background: #1890ff; color: #fff; font-size: 13px"
          @click="addBox"
          >添加板块</Button
        >
      </div>
    </template>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
export default {
  name: 'uploadPic',
  props: {
    listData: {
      type: Array,
    },
    type: {
      type: Number,
    },
  },
  components: {
    draggable: vuedraggable,
    uploadPictures,
    linkaddress,
  },
  data() {
    return {
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
      lastObj: {
        name: '',
        pic: '',
        url: '',
      },
    };
  },
  mounted() {},
  watch: {
    configObj: {
      handler(nVal, oVal) {},
      deep: true,
    },
  },
  methods: {
    linkUrl(e) {
      this.listData[this.activeIndex].url = e;
    },
    getLink(index) {
      this.activeIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    addBox() {
      if (this.listData.length == 0) {
        this.listData.push(this.lastObj);
      } else {
        let obj = JSON.parse(JSON.stringify(this.listData[this.listData.length - 1]));
        obj.name = '';
        obj.pic = '';
        obj.url = '';
        this.listData.push(obj);
      }
      // this.$emit('parentFun',this.listData)
    },
    // 点击图文封面
    modalPicTap(title, index) {
      this.activeIndex = index;
      this.modalPic = true;
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
            cssRules: 'width:960px;height:550px;padding:20px;',
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
        this.listData[this.activeIndex].pic = pc.att_dir;
        this.modalPic = false;
      });
    },
    // 删除
    bindDelete(item, index) {
      if (this.listData.length == 1) {
        this.lastObj = this.listData[0];
      }
      this.listData.splice(index, 1);
      // this.$emit('parentFun',this.listData)
    },
  },
};
</script>

<style scoped lang="stylus">
.hot_imgs {
  margin-bottom: 20px;

  // border-top 1px solid rgba(0,0,0,0.05)
  .title {
    padding: 13px 0;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .list-box {
    .item {
      position: relative;
      display: flex;
      margin-top: 20px;
      border: 1px dashed rgba(0, 0, 0, 0.15);
      padding: 18px 10px 18px 0;
      border-radius: 6px;

      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        cursor: move;
      }

      .img-box {
        position: relative;
        width: 70px;
        height: 70px;

        img {
          width: 100%;
          height: 100%;
        }
      }

      .info {
        flex: 1;
        margin-left: 16px;

        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;

          span {
            width: 70px;
            font-size: 13px;
          }

          .input-box {
            flex: 1;

            /deep/input {
              cursor: pointer;
            }
          }

          /deep/ .ivu-input {
            font-size: 13px !important;
          }
        }
      }

      .delect-btn {
        position: absolute;
        right: -11px;
        top: -15px;

        .iconfont-diy {
          font-size: 25px;
          color: #FF1818;
        }
      }
    }
  }

  .add-btn {
    margin-top: 24px;
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

.iconfont-diy {
  color: #DDDDDD;
  font-size: 28px;
}
</style>
