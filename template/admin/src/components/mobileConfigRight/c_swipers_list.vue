<template>
  <div class="hot_imgs">
    <div class="title" v-if="configData.title">
      {{ configData.title }}
    </div>
    <div class="list-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".move-icon">
        <div class="item" v-for="(item, index) in configData.list" :key="index">
          <div class="delect-btn" @click.stop="bindDelete(item, index)" v-if="!configData.isCube">
            <span class="iconfont-diy icondel_1"></span>
          </div>
          <div class="move-icon">
            <span class="iconfont-diy icondrag"></span>
          </div>
          <div>
            <div class="info">
              <div class="info-item">
                <span class="span">{{ item.imgTitle }}</span>
                <div class="img-box" @click="modalPicTap('单选', index)">
                  <img :src="item.img" alt="" v-if="item.img" />
                  <div class="upload-box" v-else><i class="el-icon-plus"></i></div>
                </div>
              </div>
            </div>
            <div class="info">
              <div class="info-item" v-for="(infos, key) in item.info" :key="key">
                <span class="span">{{ infos.title }}</span>
                <div class="input-box">
                  <el-input v-model="infos.value" :placeholder="infos.tips">
                    <i class="el-icon-link" slot="suffix" @click="getLink(index, key, item.info)" />
                  </el-input>
                </div>
              </div>
            </div>
          </div>
        </div>
      </draggable>
      <div>
        <el-dialog :visible.sync="modalPic" width="960px" title="上传图片">
          <uploadPictures
            :isChoice="isChoice"
            @getPic="getPic"
            :gridBtn="gridBtn"
            :gridPic="gridPic"
            v-if="modalPic"
          ></uploadPictures>
        </el-dialog>
      </div>
    </div>
    <template v-if="configData.list">
      <div class="add-btn" v-if="configData.list.length < configData.maxList">
        <el-button class="btn" type="primary" ghost @click="addBox">
          <span class="iconfont iconjiahao"></span>添加
        </el-button>
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
  name: 'c_swipers_list',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
    index: {
      type: null,
    },
  },
  components: {
    draggable: vuedraggable,
    linkaddress,
    uploadPictures,
  },
  data() {
    return {
      defaults: {},
      configData: {},
      menus: [],
      list: [
        {
          title: 'aa',
          val: '',
        },
      ],
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
      indexLast: 0,
      lastObj: {},
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
  },
  methods: {
    linkUrl(e) {
      this.configData.list[this.activeIndex].info[this.indexLast].value = e;
      if (this.defaults.name == 'pictureCube') {
        this.defaults.picStyle.picList[this.defaults.picStyle.tabVal].link = e;
      }
    },
    getLink(index, key, item) {
      this.indexLast = item.length - 1;
      if (key != item.length - 1) {
        return;
      }
      this.activeIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    addBox() {
      if (this.configData.list.length == 0) {
        this.lastObj.img = '';
        this.lastObj.info[0].value = '';
        this.configData.list.push(this.lastObj);
      } else {
        let obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]));
        obj.img = '';
        obj.info[0].value = '';
        this.configData.list.push(obj);
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
        this.configData.list[this.activeIndex].img = pc.att_dir;
        let data = this.defaults.menuConfig;
        if (data && data.isCube) {
          this.defaults.picStyle.picList.splice(this.defaults.picStyle.tabVal, 1, {
            image: pc.att_dir,
            link: data.list[0].info[0].value,
          });
        }
        this.modalPic = false;
      });
    },
    onBlur() {
      let data = this.defaults.menuConfig;
      this.defaults.picStyle.picList[this.defaults.picStyle.tabVal].link = data.list[0].info[0].value;
    },
    // 删除
    bindDelete(item, index) {
      if (this.configData.list.length == 1) {
        this.lastObj = this.configData.list[0];
      }
      this.configData.list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .ivu-input-icon {
  color: #bbbbbb;
}

::v-deep .ivu-input-word-count {
  color: #bbbbbb;
}

.hot_imgs {
  margin: 0 15px 20px 15px;

  .title {
    padding-bottom: 21px;
    color: #999;
    font-size: 12px;
  }

  .list-box {
    .item {
      position: relative;
      display: flex;
      background: #f9f9f9;
      align-items: center;
      padding: 16px 20px 16px 0;
      margin-bottom: 16px;
      border-radius: 3px;

      .delect-btn {
        position: absolute;
        right: -13px;
        top: -16px;

        .iconfont-diy {
          font-size: 25px;
          color: #ccc;
        }
      }

      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        cursor: move;
      }

      .img-box {
        position: relative;
        width: 64px;
        height: 64px;

        img {
          width: 100%;
          height: 100%;
          border-radius: 3px;
        }
      }

      .info {
        flex: 1;
        margin-left: 19px;

        & ~ .info {
          margin-top: 13px;
        }

        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;

          &:nth-last-child(1) {
            margin-bottom: 0;
          }

          .span {
            width: 40px;
            font-size: 12px;
            color: #999;
          }

          .input-box {
            width: 270px;
          }
        }
      }
    }
  }

  .add-btn {
    margin-top: 10px;

    .btn {
      width: 100%;
      height: 36px;
      border-color: #eeeeee;
      color: #666666;

      .iconfont {
        font-size: 11px;
        margin-right: 5px;
      }
    }
  }
}

.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #eee;
  color: #ccc;
}

.iconfont-diy {
  color: #dddddd;
  font-size: 28px;
}
</style>
