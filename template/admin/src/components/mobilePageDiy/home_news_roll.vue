<template>
  <div
    class="news-box"
    :class="{ pageOn: bgStyle === 1 }"
    :style="{ margin: '0 ' + prConfig + 'px', marginTop: slider + 'px', background: bgColor }"
    v-if="list.length"
  >
    <div class="item" :style="{ color: txtColor }">
      <div class="img-box"><img :src="imgUrl" alt="" /></div>
      <div class="right-box" :style="{ textAlign: textStyle }">{{ list[0].chiild[0].val }}</div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'home_news_roll',
  cname: '新闻播报',
  configName: 'c_news_roll',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'news', // 外面匹配名称
  icon: 'iconxinwenbobao1',
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
        name: 'news',
        timestamp: this.num,
        setUp: {
          tabVal: 0,
        },
        txtStyle: {
          title: '文本位置',
          name: 'txtStyle',
          type: 0,
          list: [
            {
              val: '居左',
              icon: 'icondoc_left',
              style: 'left',
            },
            {
              val: '居中',
              icon: 'icondoc_center',
              style: 'center',
            },
            {
              val: '居右',
              icon: 'icondoc_right',
              style: 'right',
            },
          ],
        },
        bgColor: {
          title: '背景颜色',
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
        txtColor: {
          title: '文字颜色',
          default: [
            {
              item: '#333',
            },
          ],
          color: [
            {
              item: '#333',
            },
          ],
        },
        listConfig: {
          title: '最多可添加10个版块；鼠标拖拽左侧圆点可调整版块顺序',
          max: 10,
          list: [
            {
              chiild: [
                {
                  title: '标题',
                  val: '标题',
                  max: 30,
                  pla: '选填，不超过30个字',
                  empty: true,
                },
                {
                  title: '链接',
                  val: '链接',
                  max: 200,
                  pla: '请输入连接',
                },
              ],
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
        prConfig: {
          title: '背景边距',
          val: 0,
          min: 0,
        },
        // 页面间距
        mbConfig: {
          title: '页面间距',
          val: 0,
          min: 0,
        },
        logoConfig: {
          header: '图标设置',
          title: '最多可添加1张图片，建议宽度130 * 36px',
          url: require('@/assets/images/news.png'),
        },
      },
      tabVal: '',
      bgColor: [],
      txtColor: [],
      rollStyle: '',
      txtPosition: '',
      pageData: {},
      list: [],
      imgUrl: '',
      textStyle: '',
      slider: 0,
      bgStyle: 0,
      prConfig: 0,
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
        this.list = data.listConfig.list;
        this.imgUrl = data.logoConfig.url;
        this.textStyle = data.txtStyle.list[data.txtStyle.type].style;
        this.slider = data.mbConfig.val;
        this.bgColor = data.bgColor.color[0].item;
        this.txtColor = data.txtColor.color[0].item;
        this.bgStyle = data.bgStyle.type;
        this.prConfig = data.prConfig.val;
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.pageOn{
    border-radius 6px!important
}
.news-box
   .item
        display flex
        align-items center
        height 30px
        margin 0 7px;
        .img-box
            width 75px
            height 18px
            border-right 1px solid #ddd
            padding-right 10px
            img
               width 100%
               height 100%
        .right-box
            flex 1
            padding 0 20px 0 10px
            overflow: hidden;
            text-overflow:ellipsis;
            white-space: nowrap;
            background-image url("~@/assets/images/right.png");
            background-size 20px 20px
            background-position right center
            background-repeat no-repeat
</style>
