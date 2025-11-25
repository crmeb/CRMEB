<template>
  <div>
    <div
      class="seckill-box"
      :style="{
        background: bottomBgColor,
        marginTop: mTop + 'px',
        paddingTop: topConfig + 'px',
        paddingBottom: bottomConfig + 'px',
        paddingLeft: prConfig + 'px',
        paddingRight: prConfig + 'px',
      }"
    >
      <div
        class="hd"
        :style="
          (styleConfig
            ? 'backgroundImage:url(' + imgBgUrl + ')'
            : `background:linear-gradient(90deg,${headerBgColorLeft} 0%,${headerBgColorRight} 100%)`) +
          ';borderRadius:' +
          bgRadius
        "
      >
        <div class="left acea-row row-middle">
          <div
            class="text"
            v-if="titleConfig"
            :style="
              (titleTabVal == 2 ? 'fontStyle:' : 'fontWeight:') +
              titleText +
              ';color:' +
              titleColor +
              ';fontSize:' +
              titleNumber +
              'px;'
            "
          >
            {{ titleTxtConfig }}
          </div>
          <img v-else :src="styleConfig ? imgUrl : imgColorUrl" alt="" />
          <div
            class="line"
            :style="{
              background: dividerColor,
            }"
          ></div>
          <div
            class="tips"
            :style="{
              color: styleConfig ? tipsColor : tipsColor2,
            }"
          >
            {{ tipTxt }}
          </div>
        </div>
        <div
          class="right"
          :style="{
            color: styleConfig ? headerBntColor : headerBntColor2,
            fontSize: bntNumber + 'px',
          }"
        >
          {{ rightBntTxt }}
          <span
            class="iconfont iconjinru"
            :style="{
              fontSize: bntNumber + 'px',
            }"
          ></span>
        </div>
      </div>
      <div
        class="list-wrapper"
        :class="
          goodStyleConfig == 0
            ? 'on'
            : goodStyleConfig == 1 || goodStyleConfig == 2
            ? 'on2'
            : goodStyleConfig == 3
            ? 'on3'
            : ''
        "
        :style="{
          background: bgColor,
          borderRadius: bgRadius2,
        }"
      >
        <div v-if="goodStyleConfig == 0" class="itemOne acea-row" v-for="(item, index) in numberConfig" :key="index">
          <div
            class="empty-box"
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="../../assets/images/shan.png" />
          </div>
          <div class="text">
            <div class="top">
              <div
                class="name line2"
                v-if="checkboxInfo.indexOf(0) != -1"
                :style="{
                  fontWeight: goodsName,
                  color: goodsNameColor,
                }"
              >
                熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
              </div>
              <div
                class="num"
                v-if="checkboxInfo.indexOf(1) != -1"
                :style="{
                  color: toneConfig ? joinNumColor : colorStyle.theme,
                }"
              >
                <span class="iconfont iconic_fire"></span>1223人正在参与
              </div>
            </div>
            <div
              class="bottom"
              :class="checkboxInfo.indexOf(2) != -1 && checkboxInfo.indexOf(3) != -1 ? '' : 'acea-row row-bottom'"
            >
              <div
                class="price"
                v-if="checkboxInfo.indexOf(2) != -1"
                :style="{
                  color: toneConfig ? bargainPriceColor : colorStyle.theme,
                }"
              >
                <span class="label">¥</span><span class="num">2690.00</span>
              </div>
              <div
                class="yprice"
                v-if="checkboxInfo.indexOf(3) != -1"
                :style="{
                  color: goodsPriceColor,
                }"
              >
                ¥1233423.00
              </div>
            </div>
            <div
              class="bnt"
              v-if="!bargainConfig"
              :style="{
                color: toneConfig ? goodsBntTxtColor : '#fff',
                background: toneConfig
                  ? `linear-gradient(270deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                  : themeColor,
              }"
            >
              参与砍价
            </div>
          </div>
        </div>
        <div class="itemTwo" v-if="goodStyleConfig == 1" v-for="(item2, index2) in numberConfig" :key="index2">
          <div
            class="empty-box"
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="../../assets/images/shan.png" />
          </div>
          <div
            :class="
              (checkboxInfo.indexOf(0) != -1 && checkboxInfo.length == 1 && !bargainConfig) ||
              (checkboxInfo.indexOf(0) != -1 &&
                checkboxInfo.indexOf(1) != -1 &&
                checkboxInfo.length == 2 &&
                !bargainConfig)
                ? 'item'
                : (!checkboxInfo.length || (checkboxInfo.indexOf(1) != -1 && checkboxInfo.length == 1)) &&
                  !bargainConfig
                ? 'item2'
                : ''
            "
          >
            <div
              class="title line1"
              v-if="checkboxInfo.indexOf(0) != -1"
              :style="{
                fontWeight: goodsName,
                color: goodsNameColor,
              }"
            >
              熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
            </div>
            <div
              class="price"
              :class="checkboxInfo.indexOf(3) == -1 && !bargainConfig ? 'on' : ''"
              v-if="checkboxInfo.indexOf(2) != -1"
              :style="{
                color: toneConfig ? bargainPriceColor : colorStyle.theme,
              }"
            >
              ¥<span class="num">3200.00</span>
            </div>
            <div
              class="yprice"
              :class="checkboxInfo.indexOf(2) == -1 && !bargainConfig ? 'on' : ''"
              v-if="checkboxInfo.indexOf(3) != -1"
              :style="{
                color: goodsPriceColor,
              }"
            >
              ¥3699.00
            </div>
            <div
              class="bnt"
              :class="checkboxInfo.indexOf(2) == -1 && !bargainConfig ? 'on' : ''"
              v-if="!bargainConfig"
              :style="{
                color: toneConfig ? goodsBntTxtColor : '#fff',
                background: toneConfig
                  ? `linear-gradient(90deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                  : themeColor,
              }"
            >
              去砍价
            </div>
          </div>
        </div>
        <div v-if="goodStyleConfig == 2" class="list-item" v-for="(item, index) in numberConfig" :key="index">
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div
            class="title line1"
            v-if="checkboxInfo.indexOf(0) != -1"
            :style="{
              fontWeight: goodsName,
              color: goodsNameColor,
            }"
          >
            熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
          </div>
          <div
            class="price"
            v-if="checkboxInfo.indexOf(2) != -1"
            :style="{
              color: toneConfig ? bargainPriceColor : colorStyle.theme,
            }"
          >
            低至<span class="lable">¥</span><span class="num">350.00</span>
          </div>
          <div
            class="yprice"
            v-if="checkboxInfo.indexOf(3) != -1"
            :style="{
              color: goodsPriceColor,
            }"
          >
            ¥3699.00
          </div>
        </div>
        <div class="itemThree" v-if="goodStyleConfig == 3" v-for="(item2, index2) in numberConfig" :key="index2">
          <div
            class="empty-box"
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="../../assets/images/shan.png" />
          </div>
          <div>
            <div
              class="title line1"
              v-if="checkboxInfo.indexOf(0) != -1"
              :style="{
                fontWeight: goodsName,
                color: goodsNameColor,
              }"
            >
              熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
            </div>
            <div
              class="joinNum"
              v-if="checkboxInfo.indexOf(1) != -1"
              :style="{
                color: toneConfig ? joinNumColor2 : '#fff',
                background: toneConfig
                  ? `linear-gradient(90deg,${joinBgColorLeft} 0%,${joinBgColorRight} 100%)`
                  : themeColor2,
              }"
            >
              175人参与活动
            </div>
            <div
              class="price"
              :class="checkboxInfo.indexOf(3) == -1 && !bargainConfig ? 'on' : ''"
              v-if="checkboxInfo.indexOf(2) != -1"
              :style="{
                color: toneConfig ? bargainPriceColor : colorStyle.theme,
              }"
            >
              ¥<span class="num">3200.00</span>
            </div>
            <div
              class="yprice"
              :class="checkboxInfo.indexOf(2) == -1 && !bargainConfig ? 'on' : ''"
              v-if="checkboxInfo.indexOf(3) != -1"
              :style="{
                color: goodsPriceColor,
              }"
            >
              ¥3699.00
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
import Setting from '@/setting';
export default {
  name: 'home_bargain',
  cname: '砍价',
  icon: '#iconzujian-kanjia',
  configName: 'c_home_bargain',
  type: 1, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'bargain', // 外面匹配名称
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
        cname: '砍价',
        name: 'bargain',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '头部设置',
        titleGoodsList: '商品列表',
        titleGoods: '商品设置',
        titleRight: '头部样式',
        titleGoodsStyle: '商品样式',
        titleCurrency: '通用样式',
        styleConfig: {
          title: '选择风格',
          tabVal: 1,
          tabList: [
            {
              name: '背景色',
            },
            {
              name: '背景图片',
            },
          ],
        },
        imgBgConfig: {
          info: '建议：710px * 96px',
          url: Setting.apiBaseURL.replace(/adminapi/, '') + 'statics/images/bargainBg.png',
          type: 'code',
          delType: 0,
          name: '背景图片',
        },
        titleConfig: {
          title: '标题类型',
          tabVal: 0,
          tabList: [
            {
              name: '图片',
            },
            {
              name: '文字',
            },
          ],
        },
        imgConfig: {
          info: '建议：154px * 32px',
          url: require('@/assets/images/bargain02.png'),
          type: 'code',
          delType: 0,
          name: '标题图片',
        },
        imgColorConfig: {
          info: '建议：154px * 32px',
          url: require('@/assets/images/bargain01.png'),
          type: 'code',
          delType: 0,
          name: '标题图片',
        },
        titleTxtConfig: {
          title: '标题文字',
          value: '疯狂砍价',
          place: '请输入标题文字',
          max: 6,
        },
        tipTxtConfig: {
          title: '提示文字',
          value: '低至0元免费拿',
          place: '请输入提示文字',
          max: 10,
        },
        rightBntConfig: {
          title: '右侧按钮',
          value: '更多',
          place: '请输入右侧按钮',
          max: 4,
        },
        goodStyleConfig: {
          title: '选择风格',
          tabVal: 0,
          tabList: [
            {
              name: '单列展示',
            },
            {
              name: '两列展示(纵向)',
            },
            {
              name: '三列展示',
            },
            {
              name: '左右滑动展示',
            },
          ],
        },
        numberConfig: {
          title: '商品数量',
          val: 3,
          min: 1,
        },
        checkboxInfo: {
          title: '展示信息',
          name: 'checkboxInfo',
          type: [0, 1, 2, 3],
          list: [
            {
              id: 0,
              name: '商品名称',
            },
            {
              id: 1,
              name: '参与人数',
            },
            {
              id: 2,
              name: '商品价格',
            },
            {
              id: 3,
              name: '划线价',
            },
          ],
        },
        bargainConfig: {
          title: '砍价按钮',
          tabVal: 0,
          tabList: [
            {
              name: '显示',
            },
            {
              name: '隐藏',
            },
          ],
        },
        headerBgColor: {
          title: '背景颜色',
          name: 'headerBgColor',
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
        titleText: {
          title: '标题文字',
          tabVal: 0,
          tabList: [
            {
              name: '加粗',
              style: 'bold',
            },
            {
              name: '正常',
              style: 'normal',
            },
            {
              name: '倾斜',
              style: 'italic',
            },
          ],
        },
        titleColor: {
          title: '标题颜色',
          name: 'titleColor',
          default: [
            {
              item: '#282828',
            },
          ],
          color: [
            {
              item: '#282828',
            },
          ],
        },
        titleNumber: {
          title: '标题字号',
          val: 16,
          min: 0,
        },
        headerBntColor: {
          title: '按钮颜色',
          name: 'headerBntColor',
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
        headerBntColor2: {
          title: '按钮颜色',
          name: 'headerBntColor2',
          default: [
            {
              item: '#999',
            },
          ],
          color: [
            {
              item: '#999',
            },
          ],
        },
        bntNumber: {
          title: '按钮字号',
          val: 12,
          min: 0,
        },
        tipsColor: {
          title: '提示文字',
          name: 'tipsColor',
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
        tipsColor2: {
          title: '提示文字',
          name: 'tipsColor2',
          default: [
            {
              item: '#999',
            },
          ],
          color: [
            {
              item: '#999',
            },
          ],
        },
        dividerColor: {
          title: '分割线',
          name: 'dividerColor',
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
        filletImg: {
          title: '图片圆角',
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
        goodsName: {
          title: '商品名称',
          tabVal: 1,
          tabList: [
            {
              name: '加粗',
              style: 'bold',
            },
            {
              name: '正常',
              style: 'normal',
            },
          ],
        },
        goodsNameColor: {
          title: '商品名称',
          name: 'goodsNameColor',
          default: [
            {
              item: '#333333',
            },
          ],
          color: [
            {
              item: '#333333',
            },
          ],
        },
        goodsPriceColor: {
          title: '划线价',
          name: 'goodsPriceColor',
          default: [
            {
              item: '#999999',
            },
          ],
          color: [
            {
              item: '#999999',
            },
          ],
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
        joinNumColor: {
          title: '参与人数',
          name: 'joinNumColor',
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
        joinNumColor2: {
          title: '参与人数',
          name: 'joinNumColor2',
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
        joinBgColor: {
          title: '参与背景',
          name: 'progressColor',
          default: [
            {
              item: '#FF7931',
            },
            {
              item: '#E93323',
            },
          ],
          color: [
            {
              item: '#FF7931',
            },
            {
              item: '#E93323',
            },
          ],
        },
        bargainPriceColor: {
          title: '砍价价格',
          name: 'bargainPriceColor',
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
        goodsBntColor: {
          title: '按钮颜色',
          name: 'goodsBntColor',
          default: [
            {
              item: '#FF7931',
            },
            {
              item: '#E93323',
            },
          ],
          color: [
            {
              item: '#FF7931',
            },
            {
              item: '#E93323',
            },
          ],
        },
        goodsBntTxtColor: {
          title: '按钮文字',
          name: 'goodsBntTxtColor',
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
        moduleColor: {
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
        bottomBgColor: {
          title: '底部背景',
          default: [
            {
              item: '#f5f5f5',
            },
          ],
          color: [
            {
              item: '#f5f5f5',
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
          val: 10,
          min: 0,
        },
        mbConfig: {
          title: '页面上间距',
          val: 0,
          min: 0,
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
          val: 8,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
      },
      pageData: {},
      imgUrl: '',
      imgBgUrl: '',
      tipsColor: '',
      tipsColor2: '',
      dividerColor: '',
      rightBntTxt: '',
      tipTxt: '',
      headerBntColor: '',
      headerBntColor2: '',
      bntNumber: 0,
      styleConfig: 0,
      headerBgColorLeft: '',
      headerBgColorRight: '',
      imgColorUrl: '',
      titleConfig: 0,
      titleTxtConfig: '',
      bgColor: '',
      bottomBgColor: '',
      mTop: 0,
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      titleText: '',
      titleTabVal: 0,
      checkboxInfo: [],
      imgRadius: 0,
      bgRadius: 0,
      bgRadius2: 0,
      goodsName: '',
      goodsNameColor: '',
      goodsPriceColor: '',
      toneConfig: 0,
      goodsBntColorLeft: '',
      goodsBntColorRight: '',
      goodStyleConfig: 0,
      goodsBntTxtColor: '',
      bargainConfig: 0,
      numberConfig: 1,
      titleColor: '',
      titleNumber: 0,
      joinNumColor: '',
      joinNumColor2: '',
      bargainPriceColor: '',
      joinBgColorLeft: '',
      joinBgColorRight: '',
      themeColor: '',
      themeColor2: '',
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
        this.imgUrl = data.imgConfig.url;
        this.imgBgUrl = data.imgBgConfig.url;
        this.imgColorUrl = data.imgColorConfig.url;
        this.tipsColor = data.tipsColor.color[0].item;
        this.tipsColor2 = data.tipsColor2.color[0].item;
        this.dividerColor = data.dividerColor.color[0].item;
        this.rightBntTxt = data.rightBntConfig.value;
        this.tipTxt = data.tipTxtConfig.value;
        this.headerBntColor = data.headerBntColor.color[0].item;
        this.headerBntColor2 = data.headerBntColor2.color[0].item;
        this.bntNumber = data.bntNumber.val;
        this.styleConfig = data.styleConfig.tabVal;
        this.headerBgColorLeft = data.headerBgColor.color[0].item;
        this.headerBgColorRight = data.headerBgColor.color[1].item;
        this.titleConfig = data.titleConfig.tabVal;
        this.titleTxtConfig = data.titleTxtConfig.value;
        let bgColorLeft = data.moduleColor.color[0].item;
        let bgColorRight = data.moduleColor.color[1].item;
        this.bgColor = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.mTop = data.mbConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        let tabVal = data.titleText.tabVal;
        this.titleTabVal = tabVal;
        this.titleText = data.titleText.tabList[tabVal].style;
        this.checkboxInfo = data.checkboxInfo.type;
        let filletImg = data.filletImg.type;
        let filletValImg = data.filletImg.val;
        let valListImg = data.filletImg.valList;
        this.imgRadius = filletImg
          ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
          : filletValImg + 'px';
        let fillet = data.fillet.type;
        let filletVal = data.fillet.val;
        let valList = data.fillet.valList;
        this.bgRadius = fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px 0 0'
          : filletVal + 'px ' + filletVal + 'px 0 0';
        this.bgRadius2 = fillet
          ? '0 0 ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : '0 0 ' + filletVal + 'px ' + filletVal + 'px';
        let goodsTabVal = data.goodsName.tabVal;
        this.goodsName = data.goodsName.tabList[goodsTabVal].style;
        this.goodsNameColor = data.goodsNameColor.color[0].item;
        this.goodsPriceColor = data.goodsPriceColor.color[0].item;
        this.toneConfig = data.toneConfig.tabVal;
        this.goodsBntColorLeft = data.goodsBntColor.color[0].item;
        this.goodsBntColorRight = data.goodsBntColor.color[1].item;
        this.goodStyleConfig = data.goodStyleConfig.tabVal;
        this.goodsBntTxtColor = data.goodsBntTxtColor.color[0].item;
        this.bargainConfig = data.bargainConfig.tabVal;
        this.numberConfig = data.numberConfig.val;
        this.titleColor = data.titleColor.color[0].item;
        this.titleNumber = data.titleNumber.val;
        this.joinNumColor = data.styleConfig.tabVal
          ? data.joinNumColor.color[0].item
          : data.joinNumColor2.color[0].item;
        this.joinNumColor2 = data.joinNumColor.color[0].item;
        this.bargainPriceColor = data.bargainPriceColor.color[0].item;
        this.joinBgColorLeft = data.joinBgColor.color[0].item;
        this.joinBgColorRight = data.joinBgColor.color[1].item;
        this.themeColor = `linear-gradient(90deg,${this.colorStyle.theme} 0%,${this.colorStyle.gradient} 100%)`;
        this.themeColor2 = `linear-gradient(270deg,${this.colorStyle.theme} 0%,${this.colorStyle.gradient} 100%)`;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.seckill-box {
  background: #fff;
  .hd {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-repeat: no-repeat;
    background-size: 100% 100%;
    width: 100%;
    height: 48px;
    padding: 0 12px;
    .right {
      color: #fff;
      font-size: 12px;
      .iconfont {
        font-size: 12px;
      }
    }
    .left {
      display: flex;
      align-items: center;
      .text {
        font-size: 16px;
      }
      .line {
        width: 1px;
        height: 14px;
        background: #dddddd;
        margin: 0 10px;
      }
      img {
        width: 70px;
        height: 16px;
      }
      .tips {
        font-size: 13px;
        color: #fff;
        font-weight: 400;
      }
    }
  }
  .list-wrapper {
    display: flex;
    justify-content: center;
    overflow: hidden;
    padding: 10px;
    width: 100%;
    &.on {
      display: block;
    }
    &.on2 {
      flex-wrap: wrap;
      justify-content: flex-start;
    }
    &.on3 {
      justify-content: flex-start;
      padding-right: 0;
    }
    .itemTwo,
    .itemThree {
      width: 48%;
      position: relative;
      margin-right: 11px;
      margin-top: 15px;

      .item {
        height: 50px;
      }

      .item2 {
        height: 30px;
      }

      &:nth-child(1) {
        margin-top: 0;
      }

      &:nth-child(2) {
        margin-top: 0;
      }

      &:nth-of-type(2n) {
        margin-right: 0;
      }

      .empty-box {
        width: 100%;
        height: 162px;
        background-color: #f3f9ff;
        img {
          width: 64px;
          height: 50px;
          display: block;
        }
      }
      .title {
        font-size: 14px;
        color: #333333;
        margin-top: 8px;
      }
      .price {
        font-weight: 600;
        font-size: 12px;
        &.on {
          margin-top: 8px;
        }
        .num {
          font-size: 15px;
        }
      }
      .yprice {
        font-size: 11px;
        text-decoration: line-through;
        &.on {
          margin-top: 9px;
        }
      }
      .bnt {
        width: 57px;
        height: 26px;
        border-radius: 13px;
        text-align: center;
        line-height: 26px;
        position: absolute;
        right: 0;
        bottom: 0;
        font-size: 12px;
        color: #ffffff;
        &.on {
          bottom: -4px;
        }
      }
    }

    .itemThree {
      width: 112px;
      margin-top: 0;
      margin-right: 10px !important;
      .item {
        height: 45px;
      }
      .item2 {
        height: 29px;
      }
      .empty-box {
        height: 112px;
        width: 112px;
      }
      .title {
        font-size: 13px;
        margin-top: 6px;
      }
      .joinNum {
        width: 76px;
        height: 13px;
        border-radius: 7px;
        font-size: 10px;
        text-align: center;
        line-height: 13px;
        margin-top: 3px;
      }
      .price {
        font-size: 11px;
        margin-top: 3px;
        &.on {
          margin-top: 6px;
        }
      }
    }

    .itemOne {
      position: relative;

      & ~ .itemOne {
        margin-top: 15px;
      }
      .empty-box {
        width: 140px;
        height: 140px;
        margin-right: 10px;
        background-color: #f3f9ff;
        img {
          width: 64px;
          height: 50px;
          display: block;
        }
      }
      .text {
        flex: 1;
        .top {
          height: 98px;
          .num {
            font-size: 12px;
            color: #e93323;
            .iconfont {
              font-size: 12px;
              margin-right: 2px;
            }
          }
        }
        .bottom {
          margin-top: 8px;
          height: 40px;
        }
        .name {
          font-size: 14px;
          color: #333333;
          margin-bottom: 9px;
        }
        .price {
          font-size: 12px;
          color: #e93323;
          .label {
            margin-right: 2px;
          }
          .num {
            font-size: 16px;
            font-weight: 500;
            font-family: SemiBold;
          }
        }
        .yprice {
          color: #999999;
          font-size: 11px;
          text-decoration: line-through;
        }
        .bnt {
          width: 72px;
          height: 28px;
          background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
          border-radius: 25px;
          text-align: center;
          line-height: 28px;
          color: #ffffff;
          font-size: 12px;
          position: absolute;
          right: 0;
          bottom: 0;
        }
      }
    }

    .list-item {
      width: 31.47%;
      margin-top: 10px;

      & ~ .list-item {
        margin-left: 9px;
      }

      &:nth-of-type(3n-2) {
        margin-left: 0;
      }

      &:nth-child(1),
      &:nth-child(2),
      &:nth-child(3) {
        margin-top: 0;
      }

      .img-box {
        border-radius: 6px;
        position: relative;
        width: 100%;
        height: 106px;

        .empty-box {
          background-color: #f3f9ff;
          img {
            width: 65px;
            height: 50px;
            display: block;
          }
        }
      }
      .title {
        margin-top: 8px;
        font-size: 13px;
        color: #333;
      }
      .price {
        position: relative;
        color: #fff;
        font-size: 11px;

        .lable {
          font-size: 11px;
          font-weight: 600;
          margin-left: 2px;
        }

        .num {
          font-size: 15px;
          font-weight: 600;
        }

        img {
          width: 12px;
          height: 22px;
          display: block;
          position: absolute;
          left: -4px;
          top: 0;
        }
      }
      .yprice {
        color: #999;
        font-size: 12px;
        text-decoration: line-through;
      }
    }
  }
}
</style>
