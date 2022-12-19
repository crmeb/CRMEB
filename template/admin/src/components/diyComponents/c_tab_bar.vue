<template>
  <div class="tabBars">
    <div class="title">{{ datas[name].title }}</div>
    <draggable class="dragArea list-group" :list="datas[name].list" group="peoples" handle=".iconfont">
      <div class="box-item" v-for="(item, index) in datas[name].list" :key="index">
        <div class="left-tool">
          <span class="iconfont icondrag2"></span>
        </div>
        <div class="right-wrapper">
          <div class="img-wrapper">
            <div class="img-item" v-for="(img, j) in item.imgList" @click="modalPicTap('单选', index, j)">
              <img :src="img" alt="" v-if="img" />
              <p class="txt" v-if="img">{{ j == 0 ? '选中' : '未选中' }}</p>
              <div class="empty-img" v-else>
                <span class="iconfont iconjiahao"></span>
                <p>{{ j == 0 ? '选中' : '未选中' }}</p>
              </div>
            </div>
          </div>
          <div class="c_row-item">
            <Col class="label" span="4"> 名称 </Col>
            <Col span="19" class="slider-box">
              <Input v-model="item.name" placeholder="选填不超过10个字" />
            </Col>
          </div>
          <div class="c_row-item">
            <Col class="label" span="4"> 链接 </Col>
            <Col span="19" class="slider-box">
              <Input v-model="item.link" placeholder="选填不超过10个字" />
            </Col>
          </div>
        </div>
        <div class="del-box" @click="deleteMenu(index)">
          <span class="iconfont iconcha"></span>
        </div>
      </div>
    </draggable>
    <div class="add-btn" v-if="datas[name].list.length < 5">
      <Button
        type="primary"
        ghost
        style="width: 100%; height: 40px; border-color: #1890ff; color: #1890ff"
        @click="addMenu"
        >添加图文导航
      </Button>
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
import vuedraggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
export default {
  name: 'c_tab_bar',
  props: {
    name: {
      type: String,
    },
    configData: {
      type: null,
    },
    configNum: {
      type: Number | String,
      default: 'default',
    },
  },
  components: {
    uploadPictures,
    draggable: vuedraggable,
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
      isSelect: 0,
      datas: this.configData[this.configNum],
      lastObj: {},
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
    // 添加模块
    addMenu() {
      if (this.configData[this.configNum][this.name].list.length == 0) {
        this.configData[this.configNum][this.name].list.push(this.lastObj);
      } else {
        let obj = JSON.parse(
          JSON.stringify(
            this.configData[this.configNum][this.name].list[this.configData[this.configNum][this.name].list.length - 1],
          ),
        );
        this.configData[this.configNum][this.name].list.push(obj);
      }
    },
    deleteMenu(index) {
      this.$Modal.confirm({
        title: '提示',
        content: '是否确定删除该菜单',
        onOk: () => {
          if (this.configData[this.configNum][this.name].list.length == 1) {
            this.lastObj = this.configData[this.configNum][this.name].list[0];
          }
          this.configData[this.configNum][this.name].list.splice(index, 1);
        },
        onCancel: () => {},
      });
    },
    // 点击图文封面
    modalPicTap(title, index, select) {
      this.activeIndex = index;
      this.modalPic = true;
      this.isSelect = select;
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        this.configData[this.configNum][this.name].list[this.activeIndex].imgList[this.isSelect] = pc.att_dir;
        this.modalPic = false;
      });
    },
  },
};
</script>
<style scoped lang="stylus">
.tabBars .box-item:last-child{
    margin-bottom 20px;
}
.tabBars
       .title
            margin-bottom: 10px;
            padding-bottom 10px
            border-bottom:1px solid rgba(0,0,0,0.05);
            font-size:12px;
            color:#999;
       .box-item
            position relative
            display flex
            margin-top 15px
            padding 20px 30px 20px 0
            border 1px solid #DDDDDD;
            border-radius 3px;
            .del-box
                position absolute
                right -13px
                top -18px
                cursor pointer
                .iconfont
                    color #999
                    font-size 30px
            .left-tool
                display flex
                align-items center
                justify-content center
                width 72px
                .iconfont
                    color #999
                    font-size 36px
                    cursor move
            .right-wrapper
                flex 1
                .img-wrapper
                    display flex
                    .img-item
                        position relative
                        width 80px
                        height 80px
                        margin-right 20px
                        cursor pointer
                        img
                            display block
                            width 100%
                            height 100%
                        .empty-img
                            display flex
                            align-items center
                            justify-content center
                            flex-direction column
                            width 100%
                            height 100%
                            background #f7f7f7
                            font-size 12px
                            color #BFBFBF
                            .iconfont
                                font-size 16px
                        .txt
                            position absolute
                            left 0
                            bottom 0
                            width 100%
                            height 22px
                            line-height 22px
                            text-align center
                            background: rgba(0, 0, 0, 0.4)
                            color #fff
                            font-size 12px
                .c_row-item
                    margin-top 10px
       .add-btn
            margin-bottom 20px
            width 100%
            height 40px
</style>
