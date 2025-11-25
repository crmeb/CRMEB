<template>
  <div class="footer" v-if="footConfig">
    <p class="tips">图片建议宽度81*81px；鼠标拖拽左侧圆点可调整导航顺序</p>
    <draggable class="dragArea list-group" :list="footConfig" group="peoples" handle=".iconfont">
      <div class="box-item" v-for="(item, index) in footConfig" :key="index">
        <div class="left-tool">
          <span class="iconfont iconxingzhuangjiehe"></span>
        </div>
        <div class="right-wrapper">
          <div class="acea-row" v-if="navStyle != 1">
            <div class="title">图标</div>
            <div class="img-wrapper">
              <div class="img-item" v-for="(img, j) in item.imgList" @click="modalPicTap(index, j)">
                <div class="pictrue" v-if="img">
                  <img :src="img" alt="" />
                  <p class="txt">替换</p>
                </div>
                <div class="empty-img" v-else>
                  <span class="iconfont iconjiahao"></span>
                </div>
                <div class="name">{{ j == 0 ? '选中' : '未选中' }}</div>
              </div>
            </div>
          </div>
          <div class="c_row-item" v-if="navStyle != 2">
            <el-col class="label" :span="4"> 名称 </el-col>
            <el-col class="slider-box" :span="20">
              <el-input v-model="item.name" placeholder="选填不超过10个字" />
            </el-col>
          </div>
          <div class="c_row-item">
            <el-col class="label" :span="4"> 链接 </el-col>
            <el-col class="slider-box" :span="20">
              <div>
                <el-input v-model="item.link" placeholder="选填不超过10个字">
                  <i class="el-icon-link" slot="suffix" @click="getLink(index)" />
                </el-input>
              </div>
            </el-col>
          </div>
        </div>
        <div class="del-box" @click="deleteMenu(index)">
          <span class="iconfont iconcha"></span>
        </div>
      </div>
    </draggable>
    <el-button class="add-btn" @click="addMenu" v-if="footConfig.length < 5">+ 添加板块</el-button>
    <div>
      <el-dialog :visible.sync="modalPic" width="960px" title="上传底部菜单" :mask-closable="false" :z-index="1">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
export default {
  name: 'c_foot',
  props: {
    configObj: {
      type: Object,
      default: function () {
        return {};
      },
    },
    configNme: {
      type: String,
      default: '',
    },
  },
  components: {
    uploadPictures,
    linkaddress,
    draggable: vuedraggable,
  },
  data() {
    return {
      val1: '',
      val2: '',
      footConfig: [],
      modalPic: false,
      isChoice: '单选',
      itemIndex: 0,
      itemChildIndex: 0,
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
      navStyle: 0,
      noPic: require('../../assets/images/noPictrue.png'),
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.footConfig = nVal[this.configNme];
        this.navStyle = nVal.navStyleConfig.tabVal;
      },
      deep: true,
    },
  },
  created() {
    this.footConfig = this.configObj[this.configNme];
  },
  methods: {
    linkUrl(e) {
      this.footConfig[this.itemIndex].link = e;
    },
    getLink(index) {
      this.itemIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    // 点击图文封面
    modalPicTap(parent, child) {
      this.itemIndex = parent;
      this.itemChildIndex = child;
      this.modalPic = true;
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        this.footConfig[this.itemIndex].imgList[this.itemChildIndex] = pc.att_dir;
        this.modalPic = false;
        this.$store.commit('mobildConfig/footUpdata', this.footConfig);
      });
    },
    // 添加模块
    addMenu() {
      let obj = {
        imgList: [this.noPic, this.noPic],
        name: '自定义',
        link: '',
      };
      this.footConfig.push(obj);
    },
    deleteMenu(index) {
      this.$msgbox({
        title: '提示',
        message: '是否确定删除该菜单',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '删除',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          this.footConfig.splice(index, 1);
        })
        .catch(() => {});
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep.ivu-input {
  font-size: 12px !important;
}
.dragArea {
  padding-bottom: 20px;
}
.footer {
  padding: 0 15px;
}
.tips {
  font-size: 12px;
  color: #bbbbbb;
}
.box-item {
  position: relative;
  display: flex;
  margin-top: 15px;
  padding: 20px 20px 20px 0;
  background: #f9f9f9;
  border-radius: 3px;
  .del-box {
    position: absolute;
    right: -13px;
    top: -18px;
    cursor: pointer;
    .iconfont {
      color: #ccc;
      font-size: 24px;
    }
  }
}
.left-tool {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  .iconfont {
    color: #999;
    font-size: 18px;
    cursor: move;
  }
}
.right-wrapper {
  flex: 1;
  .title {
    color: #999999;
    font-size: 12px;
    width: 50px;
    margin-top: 23px;
  }
  .img-wrapper {
    display: flex;
    .img-item {
      width: 64px;
      margin-right: 20px;
      .name {
        color: #bbbbbb;
        font-size: 12px;
        text-align: center;
        margin-top: 7px;
      }
      .pictrue {
        width: 100%;
        height: 64px;
        cursor: pointer;
        border: 1px solid #eeeeee;
        position: relative;
        border-radius: 3px;
      }
      img {
        display: block;
        width: 100%;
        height: 100%;
      }
      .empty-img {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        width: 100%;
        height: 100%;
        font-size: 12px;
        color: #bfbfbf;
        border: 1px solid #eeeeee;
        border-radius: 3px;
        .iconfont {
          font-size: 24px;
        }
      }
      .txt {
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 22px;
        line-height: 22px;
        text-align: center;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        font-size: 12px;
        border-radius: 0 0 3px 3px;
      }
    }
  }
  .c_row-item {
    margin-top: 10px;
    .label {
      color: #999999;
      font-size: 12px;
    }
  }
}
.add-btn {
  width: 100%;
  height: 40px;
  margin-bottom: 30px;
}
</style>
