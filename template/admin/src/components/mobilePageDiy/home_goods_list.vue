<template>
  <div :style="{ padding: '0 ' + prConfig + 'px' }">
    <div
      class="mobile-page paddingBox"
      :class="bgStyle === 0 ? '' : 'pageOn'"
      :style="{ marginTop: slider + 'px', background: activeColor }"
    >
      <div class="home_product">
        <!-- 单列 -->
        <template v-if="itemStyle == 0">
          <div class="list-wrapper itemA" v-if="list.length > 0">
            <div class="item" :class="conStyle ? '' : 'itemOn'" v-for="(item, index) in list" :index="index">
              <div class="img-box">
                <img v-if="item.image" :src="item.image" alt="" />
                <div v-else class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }" v-if="index == 0">标签</div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line2" v-if="titleShow">{{ item.store_name }}</div>
                  <div class="old-price" v-if="opriceShow">¥{{ item.ot_price }}</div>
                </div>
                <div class="price" :style="{ color: fontColor }">
                  <div class="num" v-if="priceShow">￥{{ item.price }}</div>
                  <div
                    class="label"
                    :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                    :class="priceShow ? '' : 'on'"
                    v-if="item.couponId.length && couponShow"
                  >
                    券
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="list-wrapper itemA" v-else>
            <div class="item" :class="conStyle ? '' : 'itemOn'">
              <div class="img-box">
                <div class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }">砍价</div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line2" v-if="titleShow">商品名称</div>
                  <div class="old-price" v-if="opriceShow">¥99.99</div>
                </div>
                <div class="price" :style="{ color: fontColor }">
                  <div class="num" v-if="priceShow">￥66.66</div>
                  <div
                    class="label"
                    :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                    :class="priceShow ? '' : 'on'"
                    v-if="couponShow"
                  >
                    券
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 二列 -->
        <template v-if="itemStyle == 1">
          <div class="list-wrapper itemC" v-if="list.length > 0">
            <div class="item" :class="conStyle ? '' : 'itemOn'" v-for="(item, index) in list" :index="index">
              <div class="img-box">
                <img v-if="item.image" :src="item.image" alt="" />
                <div v-else class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }" v-if="index == 0">标签</div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line1" v-if="titleShow">{{ item.store_name }}</div>
                  <div class="old-price" v-if="opriceShow">¥{{ item.ot_price }}</div>
                </div>
                <div class="price" :style="{ color: fontColor }">
                  <div class="num" v-if="priceShow">￥{{ item.price }}</div>
                  <div
                    class="label"
                    :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                    :class="priceShow ? '' : 'on'"
                    v-if="item.couponId.length && couponShow"
                  >
                    券
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="list-wrapper" v-else>
            <div class="item" :class="conStyle ? '' : 'itemOn'">
              <div class="img-box">
                <div class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }">砍价</div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line2" v-if="titleShow">商品名称</div>
                  <div class="old-price" v-if="opriceShow">¥99.99</div>
                </div>
                <div class="price" :style="{ color: fontColor }">
                  <div class="num" v-if="priceShow">￥66.66</div>
                  <div
                    class="label"
                    :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                    :class="priceShow ? '' : 'on'"
                    v-if="couponShow"
                  >
                    券
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 三列 -->
        <template v-if="itemStyle == 2">
          <div class="list-wrapper itemB" v-if="list.length > 0">
            <div class="item" :class="conStyle ? '' : 'itemOn'" v-for="(item, index) in list" :index="index">
              <div class="img-box">
                <img v-if="item.image" :src="item.image" alt="" />
                <div v-else class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }" v-if="index == 0">标签</div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line1" v-if="titleShow">{{ item.store_name }}</div>
                  <div class="old-price" v-if="opriceShow">¥{{ item.ot_price }}</div>
                </div>
                <div class="price" :style="{ color: fontColor }">
                  <div class="num" v-if="priceShow">￥{{ item.price }}</div>
                  <div
                    class="label"
                    :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                    :class="priceShow ? '' : 'on'"
                    v-if="item.couponId.length && couponShow"
                  >
                    券
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="list-wrapper itemB" v-else>
            <div class="item" :class="conStyle ? '' : 'itemOn'">
              <div class="img-box">
                <div class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }">砍价</div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line2" v-if="titleShow">商品名称</div>
                  <div class="old-price" v-if="opriceShow">¥99.99</div>
                </div>
                <div class="price" :style="{ color: fontColor }">
                  <div class="num" v-if="priceShow">￥66.66</div>
                  <div
                    class="label"
                    :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                    :class="priceShow ? '' : 'on'"
                    v-if="couponShow"
                  >
                    券
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 大图 -->
        <template v-if="itemStyle == 3">
          <div class="listBig" v-if="list.length > 0">
            <div class="itemBig" :class="conStyle ? '' : 'itemOn'" v-for="(item, index) in list" :key="index">
              <div class="img-box">
                <img v-if="item.image" :src="item.image" alt="" />
                <div v-else class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }" v-if="index == 0">标签</div>
              </div>
              <div class="name line2">
                <span
                  class="coupon"
                  :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                  v-if="item.couponId.length && couponShow"
                  >券</span
                ><span v-if="titleShow">{{ item.store_name }}</span>
              </div>
              <div class="price" :style="{ color: fontColor }">
                <span v-if="priceShow"
                  >￥<span class="num">{{ item.price }}</span></span
                ><span class="old-price" v-if="opriceShow">¥{{ item.ot_price }}</span>
              </div>
            </div>
          </div>
          <div class="listBig" v-else>
            <div class="itemBig" :class="conStyle ? '' : 'itemOn'">
              <div class="img-box">
                <div class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                <div class="label" :style="{ background: labelColor }">砍价</div>
              </div>
              <div class="name line2">
                <span
                  :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                  class="coupon"
                  v-if="couponShow"
                  >券</span
                ><span v-if="titleShow">商品名称</span>
              </div>
              <div class="price" :style="{ color: fontColor }">
                <span v-if="priceShow">￥<span class="num">66.66</span></span
                ><span class="old-price" v-if="opriceShow">¥99.99</span>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'home_goods_list',
  cname: '商品列表',
  configName: 'c_home_goods_list',
  icon: 'iconcuxiaoliebiao1',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'goodList', // 外面匹配名称
  props: {
    index: {
      type: null,
    },
    num: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
  },
  watch: {
    pageData: {
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true,
    },
    num: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        name: 'goodList',
        timestamp: this.num,
        setUp: {
          tabVal: 0,
        },
        tabConfig: {
          title: '选择模板',
          tabVal: 0,
          type: 1,
          tabList: [
            {
              name: '自动选择',
              icon: 'iconzidongxuanze',
            },
            {
              name: '手动选择',
              icon: 'iconshoudongxuanze',
            },
          ],
        },
        titleShow: {
          title: '是否显示名称',
          val: true,
        },
        opriceShow: {
          title: '是否显示原价',
          val: true,
        },
        priceShow: {
          title: '是否显示价格',
          val: true,
        },
        couponShow: {
          title: '是否显示优惠券',
          val: true,
        },
        selectConfig: {
          title: '商品分类',
          activeValue: [],
          list: [
            {
              value: '',
              label: '',
            },
            {
              value: '',
              label: '',
            },
          ],
        },
        goodsSort: {
          title: '商品排序',
          name: 'goodsSort',
          type: 0,
          list: [
            {
              val: '综合',
              icon: 'iconComm_whole',
            },
            {
              val: '销量',
              icon: 'iconComm_number',
            },
            {
              val: '价格',
              icon: 'iconComm_Price',
            },
          ],
        },
        numConfig: {
          val: 6,
        },
        themeColor: {
          title: '背景颜色',
          name: 'themeColor',
          default: [
            {
              item: '#fff',
            },
          ],
          color: [
            {
              item: '#fff',
            },
          ],
        },
        fontColor: {
          title: '价格颜色',
          name: 'fontColor',
          default: [
            {
              item: '#e93323',
            },
          ],
          color: [
            {
              item: '#e93323',
            },
          ],
        },
        labelColor: {
          title: '活动标签',
          name: 'labelColor',
          default: [
            {
              item: '#e93323',
            },
          ],
          color: [
            {
              item: '#e93323',
            },
          ],
        },
        itemStyle: {
          title: '显示类型',
          name: 'itemSstyle',
          type: 0,
          list: [
            {
              val: '单列',
              icon: 'iconzuoyoutuwen',
            },
            {
              val: '两列',
              icon: 'iconlianglie',
            },
            {
              val: '三列',
              icon: 'iconsanlie',
            },
            {
              val: '大图',
              icon: 'icondanlie',
            },
          ],
        },
        bgStyle: {
          title: '背景样式',
          name: 'bgStyle',
          type: 0,
          list: [
            {
              val: '直角',
              icon: 'iconPic_square',
            },
            {
              val: '圆角',
              icon: 'iconPic_fillet',
            },
          ],
        },
        conStyle: {
          title: '内容样式',
          name: 'conStyle',
          type: 1,
          list: [
            {
              val: '直角',
              icon: 'iconPic_square',
            },
            {
              val: '圆角',
              icon: 'iconPic_fillet',
            },
          ],
        },
        prConfig: {
          title: '背景边距',
          val: 0,
          min: 0,
        },
        mbConfig: {
          title: '商品间距',
          val: 0,
          min: 0,
        },
        productList: {
          title: '商品列表',
          list: [],
        },
        goodsList: {
          max: 20,
          list: [],
        },
      },
      navlist: [],
      imgStyle: '',
      txtColor: '',
      slider: '',
      tabCur: 0,
      list: [],
      activeColor: '',
      fontColor: '',
      labelColor: '',
      pageData: {},
      itemStyle: 0,
      titleShow: true,
      opriceShow: true,
      priceShow: true,
      couponShow: true,
      prConfig: 0,
      bgStyle: 0,
      conStyle: 1,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data) return;
      if (data.mbConfig) {
        this.itemStyle = data.itemStyle.type || 0;
        this.activeColor = data.themeColor.color[0].item;
        this.fontColor = data.fontColor.color[0].item;
        this.labelColor = data.labelColor.color[0].item;
        this.slider = data.mbConfig.val;
        this.prConfig = data.prConfig.val;
        this.titleShow = data.titleShow.val;
        this.opriceShow = data.opriceShow.val;
        this.priceShow = data.priceShow.val;
        this.couponShow = data.couponShow.val;
        this.bgStyle = data.bgStyle.type;
        this.conStyle = data.conStyle.type;
        if (data.tabConfig.tabVal) {
          this.list = data.goodsList.list || [];
        } else {
          this.list = data.productList.list || [];
        }
      }
    },
  },
};
</script>
<style scoped lang="stylus">
.itemOn{
    border-radius 0!important
    img,.empty-box{
        border-radius 0!important
    }
    .img-box{
        .label{
            border-radius 0 0 8px 0!important
        }
    }
}
.pageOn
  border-radius 8px!important
.listBig
    width 100%;
    padding 10px 0 1px 0;
    .itemBig
        width 100%;
        margin-bottom 15px;
        background-color #fff;
        border-radius 10px
        .img-box
            width 100%;
            height 160px;
            position relative
            border-radius 50px;
            img
               width 100%
               height 160px
               border-radius 10px 10px 0 0
            .empty-box
                border-radius 8px 8px 0 0;
            .label
                position absolute
                top 0;
                left:0;
                width:59px;
                height:25px;
                line-height 25px
                text-align center
                color #fff
                font-size 12px
                border-radius 8px 0 8px 0
        .name
           font-size 15px;
           font-weight bold;
           margin-top 8px;
           padding 0 10px;
        .coupon
            width:16px;
            height:18px;
            line-height 18px
            text-align center
            font-size 12px
            margin-right 5px;
            display inline-block
        .price
            font-weight bold
            font-size 12px
            padding 0 10px;
            .num
              font-size 18px
              margin-right 5px;
            .old-price
                color #aaa!important
                font-weight normal
                text-decoration line-through
.paddingBox
    padding-bottom 0
.home_product
    .hd_nav
        display flex
        height 65px
        padding 0 5px
        .item
            display flex
            flex-direction column
            justify-content center
            width 25%
            .title
                font-size 16px
                color #282828
            .label
                width:62px;
                height:18px;
                line-height 18px
                text-align center
                background:transparent;
                border-radius:8px;
                color #999999
                font-size 12px
            &.active
                .title
                    color #FF4444
                .label
                    color #fff
                    background:linear-gradient(270deg,rgba(255,84,0,1) 0%,rgba(255,0,0,1) 100%);
    .list-wrapper
        display flex
        flex-wrap wrap
        justify-content space-between
        padding-top 10px
        .item
            width 48.5%
            margin-bottom 10px
            background-color #fff
            border-radius 10px
            .img-box
                position relative
                width 100%
                height 173px
                img,.box
                    width 100%
                    height 100%
                    border-radius:10px 0 0 10px;
                 .empty-box
                     background  #f3f5f7
                     border-radius:10px 10px 0px 0px;
                .box
                    background #D8D8D8
                .label
                    position absolute
                    left 0
                    top 0
                    width:46px;
                    height:22px;
                    border-radius:10px 0px 10px 0px;
                    color #fff
                    font-size 13px
                    text-align center
                    line-height 22px
            .info
                padding 7px 10px
                .title
                    font-size 14px
                    color #282828
                .old-price
                    color #aaa
                    font-size 13px
                    text-decoration: line-through;
                .price
                    display flex
                    align-items center
                    .num
                        font-size 18px
                        span
                            font-size 12px
                    .label
                        width:16px;
                        height:18px;
                        margin-left 5px
                        text-align center
                        line-height 18px
                        font-size 11px
                        &.on
                           margin-left 0


        &.itemA
            /*background #fff*/
            .item
                display flex
                width 100%
                .img-box
                    position relative
                    width 110px
                    height 110px
                    img,.box,.empty-box
                        border-radius 10px 0 0 10px

                .info
                    display flex
                    justify-content space-between
                    flex-direction column
                    flex 1
                    margin-left 5px
                    padding: 5px 10px;
        &.itemB
            justify-content inherit
            .item
                width 31.6%
                margin-right 8px
                &:nth-child(3n)
                    margin-right 0
                .img-box
                    position relative
                    width 100%
                    height 110px
                    img,.box,.empty-box
                        border-radius:10px 10px 0 0;
         &.itemC
              .item
                  img,.box,.empty-box
                      border-radius:10px 10px 0 0;
</style>
