<template>
  <div>
    <!-- <div class="i-layout-page-header">
      <PageHeader
        class="product_tabs"
        :title="$route.meta.title"
        hidden-breadcrumb
      >
        <div slot="title">
          <div style="float: left">
            <span v-text="$route.meta.title" class="mr20"></span>
          </div>
          <div style="float: right">
            <Button
              class="bnt"
              type="primary"
              @click="submit"
              :loading="loadingExist"
              >保存</Button
            >
          </div>
        </div>
      </PageHeader>
    </div> -->
    <div class="i-layout-page-header">
      <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
      <div>
        <div style="float: right">
          <Button class="bnt" type="primary" @click="submit">保存</Button>
        </div>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt" :style="'min-height:' + clientHeight + 'px'">
      <Form :label-width="labelWidth">
        <FormItem label="选择配色方案：">
          <RadioGroup v-model="current" @on-change="changeColor">
            <Radio :label="1" border class="box">天空蓝<i class="iconfont iconxuanzhong6"></i></Radio>
            <Radio :label="2" border class="box green">生鲜绿<i class="iconfont iconxuanzhong6"></i></Radio>
            <Radio :label="3" border class="box red">热情红<i class="iconfont iconxuanzhong6"></i></Radio>
            <Radio :label="4" border class="box pink">魅力粉<i class="iconfont iconxuanzhong6"></i></Radio>
            <Radio :label="5" border class="box orange">活力橙<i class="iconfont iconxuanzhong6"></i></Radio>
          </RadioGroup>
        </FormItem>
        <FormItem label="当前风格示例：">
          <div class="acea-row row-top">
            <div class="pictrue" v-for="(item, index) in picList" :key="index">
              <img :src="item.image" />
            </div>
          </div>
        </FormItem>
      </Form>
    </Card>
    <!--<div class="footer acea-row row-center-wrapper">-->
    <!--<Button type="primary" @click="submit">保存</Button>-->
    <!--</div>-->
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { colorChange, getColorChange } from '@/api/diy';
export default {
  name: 'themeStyle',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      picList: [],
      picListBule: [{ image: require('@/assets/images/bule.jpg') }],
      picListGreen: [{ image: require('@/assets/images/green.jpg') }],
      picListRed: [{ image: require('@/assets/images/red.jpg') }],
      picListPink: [{ image: require('@/assets/images/pink.jpg') }],
      picListOrange: [{ image: require('@/assets/images/orange.jpg') }],
      current: '',
      clientHeight: 0,
      loadingExist: false,
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 100;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.picList = this.picListBule;
    this.getInfo();
  },
  mounted: function () {
    this.$nextTick(() => {
      this.clientHeight = `${document.documentElement.clientHeight}` - 250; //获取浏览器可视区域高度
      let that = this;
      window.onresize = function () {
        that.clientHeight = `${document.documentElement.clientHeight}` - 250;
      };
    });
  },
  methods: {
    getInfo() {
      getColorChange('color_change')
        .then((res) => {
          this.current = res.data.status ? res.data.status : 3;
          this.changeColor(this.current);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    submit() {
      this.loadingExist = true;
      colorChange(this.current, 'color_change')
        .then((res) => {
          this.loadingExist = false;
          this.$Message.success(res.msg);
        })
        .catch(() => {
          this.loadingExist = false;
        });
    },
    changeColor(e) {
      switch (e) {
        case 1:
          this.picList = this.picListBule;
          break;
        case 2:
          this.picList = this.picListGreen;
          break;
        case 3:
          this.picList = this.picListRed;
          break;
        case 4:
          this.picList = this.picListPink;
          break;
        case 5:
          this.picList = this.picListOrange;
          break;
        default:
          break;
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.box {
  height: 40px;
  width: 100px;
  line-height: 40px;
  text-align: center;
}

.bnt {
  // width 10px!important;
}

.pictrue {
  width: 800px;
  height: 100%;
  margin: 10px 24px 0 0;

  img {
    width: 100%;
    height: 100%;
  }
}

.footer {
  width: 100%;
  height: 70px;
  box-shadow: 0px -2px 4px rgba(0, 0, 0, 0.03);
  background-color: #fff;
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 9;
}

/deep/.i-layout-content-main {
  margin-bottom: 0 !important;
}

/deep/.ivu-card-body {
  padding-bottom: 0 !important;
}

/deep/.ivu-radio-inner {
  background-color: #1db0fc;
  border: 0;
  border-radius: 3px;
  width: 18px;
  height: 18px;
}

/deep/.ivu-radio-wrapper-checked .iconfont {
  display: inline-block;
}

/deep/.ivu-radio-focus {
  box-shadow: unset;
  z-index: unset;
}

/deep/.ivu-radio-wrapper {
  margin-right: 18px;
}

.green /deep/.ivu-radio-inner {
  background-color: #42CA4D;
}

.red /deep/.ivu-radio-inner {
  background-color: #E93323;
}

.pink/deep/.ivu-radio-inner {
  background-color: #FF448F;
}

.orange/deep/.ivu-radio-inner {
  background-color: #FE5C2D;
}

/deep/.ivu-radio-border {
  position: relative;
}

.iconfont {
  position: absolute;
  top: 0px;
  left: 21px;
  font-size: 12px;
  display: none;
  color: #fff;
}

/deep/.ivu-radio-inner:after {
  background-color: unset;
  transform: unset;
}

/deep/.i-layout-page-header {
  height: 66px;
  background-color: #fff;
  border-bottom: 1px solid #e8eaec;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
