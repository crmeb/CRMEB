<template>
  <div v-if="footConfig">
    <p class="tips">图片建议宽度81*81px；鼠标拖拽左侧圆点可调整导航顺序</p>
    <draggable class="dragArea list-group" :list="footConfig" group="peoples" handle=".iconfont">
      <div class="box-item" v-for="(item, index) in footConfig" :key="index">
        <div class="left-tool">
          <span class="iconfont icondrag2"></span>
        </div>
        <div class="right-wrapper">
          <div class="img-wrapper">
            <div class="img-item" v-for="(img, j) in item.imgList" @click="modalPicTap(index, j)">
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
              <div @click="getLink(index)">
                <Input icon="ios-arrow-forward" v-model="item.link" readonly placeholder="选填不超过10个字" />
              </div>
            </Col>
          </div>
        </div>
        <div class="del-box" @click="deleteMenu(index)">
          <span class="iconfont iconcha"></span>
        </div>
      </div>
    </draggable>
    <Button class="add-btn" type="info" ghost @click="addMenu" v-if="footConfig.length < 5">添加图文导航</Button>
    <div>
      <Modal
        v-model="modalPic"
        width="950px"
        scrollable
        footer-hide
        closable
        title="上传底部菜单"
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
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.footConfig = nVal[this.configNme];
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
        imgList: ['', ''],
        name: '自定义',
        link: '',
      };
      this.footConfig.push(obj);
    },
    deleteMenu(index) {
      this.$Modal.confirm({
        title: '提示',
        content: '是否确定删除该菜单',
        onOk: () => {
          this.footConfig.splice(index, 1);
        },
        onCancel: () => {},
      });
    },
  },
};
</script>

<style scoped lang="stylus">
 /deep/.ivu-input{
     font-size 13px!important;
  }
.tips
    padding-bottom 5px
    font-size 12px
    color #999
    border-bottom 1px solid rgba(0, 0, 0, 0.05)
.box-item
    position relative
    display flex
    margin-top 15px
    padding 20px 30px 20px 0
    border: 1px solid #DDDDDD;
    border-radius: 3px;
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
                    background: rgba(0, 0, 0, 0.4);
                    color #fff
                    font-size 12px
        .c_row-item
            margin-top 10px
.add-btn
    margin-top 20px
    width 100%
    height 40px
</style>
