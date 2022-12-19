<template>
  <div :style="{ height: scrollerHeight + 'px' || '' }">
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form ref="formValidate" :model="formValidate" :label-width="80" label-position="left" class="tabform">
        <Row :gutter="24" type="flex" justify="end">
          <Col span="24">
            <Col v-bind="grid" class="mr">
              <FormItem label="图文搜索：" prop="cate_name" label-for="cate_name">
                <Input
                  search
                  enter-button
                  placeholder="请输入"
                  element-id="cate_name"
                  v-model="formValidate.cate_name"
                  @on-search="userSearchs"
                />
              </FormItem>
            </Col>
          </Col>
        </Row>
        <Row type="flex" v-show="$route.path === '/admin/app/wechat/news_category/index'">
          <router-link :to="'/admin/app/wechat/news_category/save/0'">
            <Button type="primary" class="bnt" icon="md-add">添加图文消息</Button>
          </router-link>
        </Row>
      </Form>
    </Card>
    <div class="contentBox">
      <div id="content" :style="{ top: contentTop + 'px' || '', width: contentWidth }" ref="content">
        <vue-waterfall-easy
          :imgsArr="imgsArr"
          :maxCols="maxCol"
          :width="screenWidth"
          @click="clickFn"
          @scrollReachBottom="getData"
          ref="waterfall"
          :reachBottomDistance="30"
        >
          <div class="img-info" slot-scope="props" v-if="props.value.new.length !== 0">
            <div v-for="(j, i) in props.value.new" :key="i">
              <div v-if="i === 0">
                <div
                  class="news_pic"
                  :style="{
                    backgroundImage: 'url(' + j.image_input[0] + ')',
                    backgroundSize: '100% 100%',
                  }"
                  @mouseenter="mouseenterOut(j)"
                  @mouseleave="mouseenterOver(j)"
                >
                  <Button
                    type="success"
                    shape="circle"
                    icon="md-create"
                    v-show="props.value.new[i].isDel && isShow"
                    @click="clkk(props.value)"
                  ></Button>
                  <Button
                    type="error"
                    shape="circle"
                    icon="md-trash"
                    v-show="props.value.new[i].isDel && isShow"
                    @click="del(props.value, '删除图文', i)"
                    style="margin-top: 5px"
                  ></Button>
                  <Button
                    type="primary"
                    icon="md-paper-plane"
                    v-show="props.value.new[i].isDel && isShowSend"
                    shape="circle"
                    @click="send(props.value, '发送', i)"
                    >推送</Button
                  >
                </div>
                <span class="news_sp">{{ j.title }}</span>
              </div>
              <div v-else class="news_cent">
                <span class="news_sp1" v-if="j.synopsis">{{ j.title }}</span>
                <div class="news_cent_img" v-if="j.image_input.length !== 0">
                  <img :src="j.image_input[0]" />
                </div>
              </div>
            </div>
            <p class="some-info">{{ props.value.id }}</p>
          </div>
          <div slot="waterfall-over"></div>
        </vue-waterfall-easy>
      </div>
    </div>
  </div>
</template>

<script>
import vueWaterfallEasy from 'vue-waterfall-easy';
import { wechatNewsListApi } from '@/api/app';
import { mapState } from 'vuex';
export default {
  name: 'newsCategory',
  props: {
    scrollerHeight: {
      type: String,
      default: '100%',
    },
    contentTop: {
      type: String,
      default: '230',
    },
    contentWidth: {
      type: String,
      default: '100%',
    },
    maxCols: {
      type: Number,
      default: 5,
    },
    isShow: {
      type: Boolean,
      default: false,
    },
    isShowSend: {
      type: Boolean,
      default: false,
    },
    userIds: {
      type: Array,
      default: () => {
        [];
      },
    },
  },
  components: {
    vueWaterfallEasy,
  },
  data() {
    return {
      isDel: false,
      imgsArr: [],
      group: 0, // 当前加载的加载图片的次数
      fetchImgsArr: [], // 存放每次滚动时下一批要加载的图片的数组
      orderData: {},
      cols: NaN, // 需要根据窗口宽度初始化
      gridPic: {
        xl: 6,
        lg: 8,
        md: 8,
        sm: 24,
        xs: 24,
      },
      grid: {
        xl: 8,
        lg: 8,
        md: 8,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        cate_name: '',
        page: 1,
        limit: 10,
      },
      screenWidth: document.body.clientWidth - 200,
      maxCol: 1,
    };
  },
  created() {
    if (this.maxCols === 5) {
      this.$set(this, 'maxCol', this.screenWidth / 240);
    } else {
      this.maxCol = this.maxCols;
    }
    console.log(this.maxCol);
    this.getData();
  },
  mounted() {},
  computed: {},
  methods: {
    // 发送图文消息
    send(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat/push`,
        method: 'post',
        ids: {
          id: row.id,
          user_ids: this.userIds,
        },
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    clickFn(event, { index, value }) {
      event.preventDefault();
      if (event.target.tagName.toLowerCase() === 'div') {
        this.$emit('getCentList', value);
      }
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat/news/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.$nextTick(() => {
            this.imgsArr = [];
          });
          this.formValidate.page = 1;
          this.getData();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除成功
    // submitModel () {
    //     if (this.delfromData.title === '删除图文') {
    //         // this.imgsArr.splice(this.delfromData.num, 1)
    //         this.$nextTick(() => {
    //             this.imgsArr = [];
    //         })
    //         this.formValidate.page = 1;
    //         this.getData();
    //     }
    // },
    // 编辑
    clkk(item) {
      this.$router.push({
        path: '/admin/app/wechat/news_category/save/' + item.id,
      });
    },
    // 鼠标移进
    mouseenterOut(item) {
      this.$set(item, 'isDel', true);
    },
    // 鼠标移出
    mouseenterOver(item) {
      this.$set(item, 'isDel', false);
    },
    // 搜索
    userSearchs() {
      this.$nextTick(() => {
        this.imgsArr = [];
      });
      this.formValidate.page = 1;
      this.getData();
    },
    // 瀑布流数据
    getData() {
      wechatNewsListApi(this.formValidate)
        .then(async (res) => {
          if (res.data.list.length === 0) {
            // 模拟已经无新数据，显示 slot="waterfall-over"
            this.imgsArr = [];
            this.$nextTick(() => {
              this.$refs.waterfall.waterfallOver();
            });
          } else {
            let num = Math.ceil(res.data.count / this.formValidate.limit) + 1;
            res.data.list.map((item) => {
              item.isDel = false;
            });
            this.imgsArr = this.imgsArr.concat(res.data.list) || [];
            this.formValidate.page++;
            if (this.formValidate.page === num) {
              // 模拟已经无新数据，显示 slot="waterfall-over"
              this.$refs.waterfall.waterfallOver();
              return;
            }
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
  computed: {},
};
</script>

<style scoped lang="stylus">
.contentBox {
  height: 100%;
  width: 100%;
  position: static;

  #content {
    position: absolute;
    /* top: 280px; */
    bottom: 0;
    width: 86%;
    /* height 1000px; */
  }
}

.contentBox >>> .vue-waterfall-easy {
  width: 100% !important;
  left: 0 !important;
  margin-left: 0 !important;
}

.contentBox >>> .vue-waterfall-easy-scroll::-webkit-scrollbar {
  display: none;
}

.contentBox >>> .vue-waterfall-easy-scroll {
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
  overflow-x: hidden;
  overflow-y: auto;
}

.some-info {
  padding: 7px;
  box-sizing: border-box;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.Refresh {
  font-size: 12px;
  color: #1890FF;
  cursor: pointer;
  line-height: 35px;
  display: inline-block;
}

.news_pic {
  width: 100%;
  height: 150px;
  overflow: hidden;
  position: relative;
  background-size: 100%;
  background-position: center center;
  border-radius: 5px 5px 0 0;
  padding: 10px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.news_sp {
  font-size: 12px;
  color: #000000;
  background: #fff;
  width: 100%;
  height: 38px;
  line-height: 38px;
  padding: 0 12px;
  box-sizing: border-box;
  display: block;
}

.news_cent {
  width: 100%;
  height: auto;
  background: #fff;
  border-top: 1px dashed #eee;
  display: flex;
  padding: 10px;
  box-sizing: border-box;
  justify-content: space-between;

  .news_sp1 {
    font-size: 12px;
    color: #000000;
    width: 71%;
  }

  .news_cent_img {
    width: 81px;
    height: 46px;
    border-radius: 6px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
    }
  }
}

.news_pic >>> .ivu-btn-error {
  width: 24px !important;
  height: 24px !important;
  background: #FF5D5F !important;
  color: #fff !important;
  border: 1px solid #eee !important;
}

.news_pic >>>.ivu-btn-error:hover {
  background: #FF5D5F !important;
  border: 1px solid #fff !important;
  color: #fff !important;
}

.news_pic >>> .ivu-btn-success {
  width: 24px !important;
  height: 24px !important;
  border: 1px solid #eee !important;
}
</style>
