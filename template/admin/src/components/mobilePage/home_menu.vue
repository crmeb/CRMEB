<template>
  <div
    :style="{
      background: bottomBgColor,
      marginTop: slider + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px',
      }"
    >
      <div class="mobile-page">
        <div class="list_menu">
          <div
            class="item"
            :class="number === 1 ? 'four' : number === 2 ? 'five' : ''"
            v-for="(item, index) in vuexMenu"
            :key="index"
            v-if="item.show"
          >
            <div class="img-box" :class="menuStyleConfig == 1 ? 'on' : ''" v-if="menuStyleConfig != 2">
              <img
                :src="item.img"
                alt=""
                v-if="item.img"
                :style="{
                  borderRadius: filletImg
                    ? valListImg[0].val +
                      'px ' +
                      valListImg[1].val +
                      'px ' +
                      valListImg[3].val +
                      'px ' +
                      valListImg[2].val +
                      'px'
                    : filletValImg + 'px',
                }"
              />
              <div
                class="empty-box on"
                :style="{
                  borderRadius: filletImg
                    ? valListImg[0].val +
                      'px ' +
                      valListImg[1].val +
                      'px ' +
                      valListImg[3].val +
                      'px ' +
                      valListImg[2].val +
                      'px'
                    : filletValImg + 'px',
                }"
                v-else
              >
                <img src="../../assets/images/shan.png" />
              </div>
            </div>
            <p v-if="menuStyleConfig != 1" :style="'color:' + textColor">{{ item.info[0].value }}</p>
          </div>
        </div>
      </div>
      <!--单行展示-->
      <!-- <div class="mobile-page" v-else>
		        <div class="home_menu">
		            <div class="menu-item" v-for="(item,index) in vuexMenu" :key="index">
		                <div class="img-box" :class="menuStyle?'on':''">
		                    <img :src="item.img" alt="" v-if="item.img">
		                    <div class="empty-box on" v-else> <span class="iconfont-diy icontupian"></span> </div>
		                </div>
		                <p :style="{color:txtColor}">{{item.info[0].value}}</p>
		            </div>
		        </div>
		    </div> -->
      <div class="dot" v-if="showConfig">
        <div class="dot-item" :style="{ background: toneConfig ? `${pointerColor}` : `${colorStyle.theme}` }"></div>
        <div class="dot-item" :style="{ background: toneConfig ? `${pointerBgColor}` : '' }" v-for="item in 2"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'home_menu',
  cname: '导航组',
  icon: '#iconzujian-daohangzu',
  configName: 'c_home_menu',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'menus', // 外面匹配名称
  props: {
    index: {
      type: null,
    },
    num: {
      type: null,
    },
    colorStyle: {
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
  // mixins: [theme],
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '导航组',
        name: 'menus',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleContent: '内容设置',
        titleRight: '图片样式',
        titlePointer: '指示器设置',
        titleCurrency: '通用样式',
        menuStyleConfig: {
          title: '导航样式',
          tabVal: 0,
          tabList: [
            {
              name: '图片加文字',
            },
            {
              name: '图片',
            },
            {
              name: '文字',
            },
          ],
        },
        number: {
          title: '单行显示',
          tabVal: 1,
          tabList: [
            {
              name: '3个',
            },
            {
              name: '4个',
            },
            {
              name: '5个',
            },
          ],
        },
        showConfig: {
          title: '展示样式',
          tabVal: 0,
          tabList: [
            {
              name: '固定显示',
            },
            {
              name: '分页滑动',
            },
          ],
        },
        rowsNum: {
          title: '显示行数',
          tabVal: 0,
          tabList: [
            {
              name: '1行',
            },
            {
              name: '2行',
            },
            {
              name: '3行',
            },
            {
              name: '4行',
            },
          ],
        },
        filletImg: {
          title: '背景圆角',
          type: 0,
          list: [
            {
              val: '全部',
              icon: 'iconcaozuo-zhengti',
            },
            {
              val: '单个',
              icon: 'iconcaozuo-bianjiao',
            },
          ],
          valName: '圆角值',
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        toneConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            {
              name: '跟随主题风格',
            },
            {
              name: '自定义',
            },
          ],
        },
        pointerBgColor: {
          title: '常规样式',
          default: [
            {
              item: '#DDDDDD',
            },
          ],
          color: [
            {
              item: '#DDDDDD',
            },
          ],
        },
        pointerColor: {
          title: '选中样式',
          default: [
            {
              item: '#E93323',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
          ],
        },
        bgColor: {
          title: '组件背景',
          default: [
            {
              item: '#fff',
            },
            {
              item: '#fff',
            },
          ],
          color: [
            {
              item: '#fff',
            },
            {
              item: '#fff',
            },
          ],
        },
        textColor: {
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
        bottomBgColor: {
          title: '底部背景',
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
        topConfig: {
          title: '上边距',
          val: 0,
          min: 0,
        },
        bottomConfig: {
          title: '下边距',
          val: 0,
          min: 0,
        },
        prConfig: {
          title: '左右边距',
          val: 0,
          min: 0,
        },
        // 页面间距
        mbConfig: {
          title: '页面上间距',
          val: 0,
          min: 0,
        },
        menuConfig: {
          title: '最多可添加1张图片，建议宽度90 * 90px',
          bnt: '添加',
          type: 1,
          maxList: 100,
          list: [
            {
              img: '',
              show: true,
              info: [
                {
                  title: '标题',
                  value: '标题',
                  tips: '选填，不超过4个字',
                  max: 4,
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                },
              ],
            },
            {
              img: '',
              show: true,
              info: [
                {
                  title: '标题',
                  value: '标题',
                  tips: '选填，不超过4个字',
                  max: 4,
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                },
              ],
            },
            {
              img: '',
              show: true,
              info: [
                {
                  title: '标题',
                  value: '标题',
                  tips: '选填，不超过4个字',
                  max: 4,
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                },
              ],
            },
            {
              img: '',
              show: true,
              info: [
                {
                  title: '标题',
                  value: '标题',
                  tips: '选填，不超过4个字',
                  max: 4,
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                },
              ],
            },
          ],
        },
        fillet: {
          title: '背景圆角',
          type: 0,
          list: [
            {
              val: '全部',
              icon: 'iconcaozuo-zhengti',
            },
            {
              val: '单个',
              icon: 'iconcaozuo-bianjiao',
            },
          ],
          valName: '圆角值',
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
      },
      vuexMenu: [],
      boxStyle: '',
      slider: '',
      bgColorLeft: '',
      bgColorRight: '',
      bottomBgColor: '',
      number: 0,
      rowsNum: 0,
      textColor: '',
      pointerColor: '',
      pointerBgColor: '',
      pageData: {},
      prConfig: 0,
      menuStyleConfig: 0,
      showConfig: 0,
      filletImg: 0,
      filletValImg: 0,
      valListImg: [],
      fillet: 0,
      filletVal: 0,
      valList: [],
      toneConfig: 0,
      topConfig: 0,
      bottomConfig: 0,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    // 对象转数组
    objToArr(data) {
      let obj = Object.keys(data);
      let m = obj.map((key) => data[key]);
      return m;
    },
    setConfig(data) {
      if (!data) return;
      if (data.mbConfig) {
        this.menuStyleConfig = data.menuStyleConfig.tabVal;
        this.showConfig = data.showConfig.tabVal;
        this.filletImg = data.filletImg.type;
        this.filletValImg = data.filletImg.val;
        this.valListImg = data.filletImg.valList;
        this.fillet = data.fillet.type;
        this.filletVal = data.fillet.val;
        this.valList = data.fillet.valList;
        this.toneConfig = data.toneConfig.tabVal;
        this.pointerColor = data.pointerColor.color[0].item;
        this.pointerBgColor = data.pointerBgColor.color[0].item;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.textColor = data.textColor.color[0].item;
        this.slider = data.mbConfig.val;
        this.prConfig = data.prConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.bgColorLeft = data.bgColor.color[0].item;
        this.bgColorRight = data.bgColor.color[1].item;
        let rowsNum = data.rowsNum.tabVal;
        let number = data.number.tabVal;
        let lists = this.objToArr(data.menuConfig.list);
        let list = [];
        lists.forEach((item) => {
          if (item.show) {
            list.push(item);
          }
        });
        this.number = number;
        this.rowsNum = rowsNum;
        if (this.showConfig) {
          this.vuexMenu = list.splice(0, (rowsNum + 1) * (number + 3));
        } else {
          this.vuexMenu = lists;
        }
      }
    },
  },
};
</script>

<style scoped lang="scss">
.list_menu {
  padding: 0 12px 12px;
  display: flex;
  flex-wrap: wrap;

  .item {
    margin-top: 12px;
    font-size: 12px;
    color: #333;
    text-align: center;
    width: 33.3333%;

    &.four {
      width: 25%;
    }

    &.five {
      width: 20%;
    }

    .img-box {
      width: 45px;
      height: 45px;
      margin: 0 auto 8px auto;

      &.on {
        margin-bottom: 0;
      }

      img {
        width: 100%;
        height: 100%;
      }
    }
  }

  .icontupian {
    font-size: 16px;
  }
}

.home_menu {
  padding: 0 12px 12px;
  display: flex;
  overflow: hidden;

  .menu-item {
    margin-top: 12px;
    font-size: 11px;
    color: #282828;
    text-align: center;
    margin-right: 27px;

    .img-box {
      width: 50px;
      height: 50px;

      &.on {
        border-radius: 50%;

        img,
        .empty-box {
          border-radius: 50%;
        }
      }
    }

    .box,
    img {
      width: 100%;
      height: 100%;
    }

    .box {
      background: #d8d8d8;
    }

    p {
      margin-top: 5px;
    }
  }

  &.on {
    .menu-item {
      margin-right: 51px;

      &:nth-child(5n) {
        margin-right: 51px;
      }

      &:nth-child(4n) {
        margin-right: 0;
      }
    }
  }

  .icontupian {
    font-size: 16px;
  }
}

.dot {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-bottom: 10px;

  &.number {
    bottom: 15px;
  }

  .num {
    width: 25px;
    height: 18px;
    line-height: 18px;
    background-color: #000;
    color: #fff;
    opacity: 0.3;
    border-radius: 8px;
    font-size: 12px;
    text-align: center;
  }

  .dot-item {
    width: 10px;
    height: 3px;
    background: #dddddd;
    border-radius: 50%;
    margin: 0 3px;
  }
}

.empty-box {
  background: #f3f9ff;

  img {
    width: 26px !important;
    height: 20px !important;
  }
}
</style>
