// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * diy自定义组件
 * */

import __asset_foot_001_0 from '@diy/assets/images/foot-001.png';
import __asset_foot_002_1 from '@diy/assets/images/foot-002.png';
import __asset_foot_003_2 from '@diy/assets/images/foot-003.png';
import __asset_foot_004_3 from '@diy/assets/images/foot-004.png';
import __asset_foot_005_4 from '@diy/assets/images/foot-005.png';
import __asset_foot_006_5 from '@diy/assets/images/foot-006.png';
import __asset_foot_007_6 from '@diy/assets/images/foot-007.png';
import __asset_foot_008_7 from '@diy/assets/images/foot-008.png';
import { defineStore } from 'pinia';
import { ref } from 'vue';

// 模块级默认模板（原变量名 bottomMenu，与 state 字段同名，转 Pinia setup store 后改名 defaultBottomMenu 以避免遮蔽）
const defaultBottomMenu = {
  cname: '底部菜单',
  name: 'bottomMenu',
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  entryConfig: {
    title: '入口内容',
    tabVal: 0,
    tabList: [{ name: '默认' }, { name: '自定义' }],
  },
  styleTitle: '样式设置',

  iconColor: {
    title: '图标颜色',
    default: [{ item: '#333' }],
    color: [{ item: '#333' }],
  },
  iconSize: {
    title: '图标大小',
    val: 20,
    min: 10,
    max: 50,
  },
  iconRotate: {
    title: '旋转角度',
    val: 0,
    min: 0,
    max: 360,
  },
  padding: {
    title: '内边距',
    val: 0,
    min: 0,
    max: 50,
  },
  contentConfigTitle: '内容设置',
  showContent: {
    title: '显示内容',
    name: 'showContent',
    type: [3, 1, 2],
    list: [
      { id: 3, name: '首页', icon: 'icon-shouye6' },
      { id: 1, name: '收藏', icon: 'icon-shoucang4' },
      { id: 2, name: '购物车', icon: 'icon-gouwuche' },
      { id: 0, name: '客服', icon: 'icon-kefu' },
      { id: 4, name: '分享', icon: 'icon-fenxiang4' },
    ],
  },
  cartButton: {
    title: '购物车按钮',
    tabVal: 0,
    tabList: [{ name: '显示' }, { name: '隐藏' }],
  },
  menuConfig: {
    title: '最多可添加1张图片，建议宽度90 * 90px',
    bnt: '添加',
    type: 1,
    listStyle: 0,
    maxList: 100,
    list: [
      {
        img: '',
        type: 0,
        show: true,
        icon: '',
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
  buttonStyleTitle: '按钮设置',
  toneConfig: {
    title: '按钮色调',
    tabVal: 0, // 0: Follow Theme, 1: Custom
    tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
  },
  cartColor: {
    title: '购物车按钮',
    default: [{ item: '#FAAD14' }, { item: '#FAAD14' }],
    color: [{ item: '#FAAD14' }, { item: '#FAAD14' }],
  },
  buyColor: {
    title: '购买按钮',
    default: [{ item: '#E93323' }, { item: '#E93323' }],
    color: [{ item: '#E93323' }, { item: '#E93323' }],
  },
  generalStyleTitle: '通用样式',
  moduleColor: {
    title: '组件背景',
    default: [{ item: '#fff' }, { item: '#fff' }],
    color: [{ item: '#fff' }, { item: '#fff' }],
  },
  bottomBgColor: {
    title: '底部背景',
    default: [{ item: '#F5F5F5' }],
    color: [{ item: '#F5F5F5' }],
  },
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#fff' }, { item: '#fff' }],
      color: [{ item: '#fff' }, { item: '#fff' }],
    },
    colorDirection: {
      title: '渐变方向',
      tabVal: 0,
      tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
    },
    imageConfig: {
      header: '背景图片',
      title: '',
      name: '上传图片',
      type: 'code',
      url: '',
      info: '建议尺寸：750px * 400px',
    },
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  borderConfig: {
    title: '边框设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Hide, 1: Show
    styleConfig: {
      title: '边框样式',
      tabVal: 0,
      tabList: [
        { name: '实线', style: 'solid' },
        { name: '虚线', style: 'dashed' },
        { name: '点状', style: 'dotted' },
      ],
    },
    widthConfig: {
      title: '边框粗细',
      val: 1,
      min: 1,
    },
    colorConfig: {
      title: '边框颜色',
      default: [{ item: '#e5e5e5' }],
      color: [{ item: '#e5e5e5' }],
    },
  },
  shadowConfig: {
    title: '阴影设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Off, 1: On
    colorConfig: {
      title: '阴影颜色',
      default: [{ item: 'rgba(0,0,0,0.1)' }],
      color: [{ item: 'rgba(0,0,0,0.1)' }],
    },
    xConfig: {
      title: 'X轴偏移',
      val: 0,
      min: -50,
    },
    yConfig: {
      title: 'Y轴偏移',
      val: 0,
      min: -50,
    },
    blurConfig: {
      title: '模糊半径',
      val: 10,
      min: 0,
    },
    spreadConfig: {
      title: '扩展半径',
      val: 0,
      min: -50,
    },
  },
  paddingConfig: {
    title: '内边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
  menuPcFillet: {
    title: '圆角设置',
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
};

export const useMobildConfigStore = defineStore(
  'mobildConfig',
  () => {
    const configName = ref('');
    const pageTitle = ref('');
    const pageName = ref('模版名称1');
    const pageShow = ref(1);
    const pageColor = ref(0);
    const pagePic = ref(0);
    const pageColorPicker = ref('#f5f5f5');
    const pageTabVal = ref(0);
    const pagePicUrl = ref('');
    // 已知组件列表默认数据 数组
    const defaultArray = ref({});
    const bottomMenu = ref(JSON.parse(JSON.stringify(defaultBottomMenu)));
    const pageFooter = ref({
      cname: '底部导航',
      name: 'pageFoot',
      setUp: {
        tabVal: 0,
      },
      titleLeft: '展示设置',
      titleNav: '导航内容',
      titleRight: '颜色设置',
      titleCurrency: '通用样式',
      effectConfig: {
        title: '展示效果',
        tabVal: 1,
        tabList: [
          {
            name: '系统默认',
          },
          {
            name: '自定义',
          },
        ],
      },
      navConfig: {
        title: '导航类型',
        tabVal: 0,
        tabList: [
          {
            name: '底部固定',
          },
          {
            name: '底部悬浮',
          },
        ],
      },
      navStyleConfig: {
        title: '导航样式',
        tabVal: 0,
        tabList: [
          {
            name: '图片+文字',
          },
          {
            name: '文字',
          },
          {
            name: '图片',
          },
        ],
      },
      toneConfig: {
        title: '色调',
        tabVal: 1,
        tabList: [
          {
            name: '跟随主题风格',
          },
          {
            name: '自定义',
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
        title: '页面下间距',
        val: 25,
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
        val: 30,
        min: 0,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      },
      txtColor: {
        title: '文字颜色',
        name: 'txtColor',
        default: [{ item: '#282828' }],
        color: [{ item: '#282828' }],
      },
      activeTxtColor: {
        title: '选中文字颜色',
        name: 'txtColor',
        default: [{ item: '#F62C2C' }],
        color: [{ item: '#F62C2C' }],
      },
      bgColor: {
        title: '背景颜色',
        name: 'bgColor',
        default: [{ item: '#fff' }],
        color: [{ item: '#fff' }],
      },
      bgColor2: {
        title: '背景颜色',
        name: 'bgColor2',
        default: [{ item: 'rgba(255,255,255,0.8)' }],
        color: [{ item: 'rgba(255,255,255,0.8)' }],
      },

      status: {
        title: '是否自定义',
        name: 'status',
        status: false,
      },

      menuList: [
        {
          imgList: [__asset_foot_001_0, __asset_foot_002_1],
          name: '首页',
          link: '/pages/index/index',
        },
        {
          imgList: [__asset_foot_003_2, __asset_foot_004_3],
          name: '分类',
          link: '/pages/goods_cate/goods_cate',
        },
        {
          imgList: [__asset_foot_005_4, __asset_foot_006_5],
          name: '购物车',
          link: '/pages/order_addcart/order_addcart',
        },
        {
          imgList: [__asset_foot_007_6, __asset_foot_008_7],
          name: '我的',
          link: '/pages/user/index',
        },
      ],
    });

    function FOOTER(data) {
      pageFooter.value.menuList[2] = data.name;
    }
    function UPBOTTOMMENU(data) {
      bottomMenu.value = data;
    }
    /**
     * @description 默认配置push到数组里面
     * @param {Object} data
     * 把默认数据添加到默认数组里面，解耦重复组件公用一条配置的问题
     */
    function ADDARRAY(data) {
      data.val.id = 'id' + data.val.timestamp;
      defaultArray.value[data.num] = data.val;
    }
    /**
     * @description 删除列表第几个默认数据
     * @param {Object} data 数据
     */
    function DELETEARRAY(data) {
      delete defaultArray.value[data.num];
    }
    /**
     * @description 删除列表第几个默认数据
     * @param {Object} data 数据
     */
    function ARRAYREAST(data) {
      delete defaultArray.value[data];
    }
    /**
     * @description 数组排序
     * @param {Object} data 位置index记录
     */
    function defaultArraySort(data) {
      let newArr = objToArr(defaultArray.value);
      let sortArr = [];
      let newObj = {};
      function objToArr(data) {
        let obj = Object.keys(data);
        let m = obj.map((key) => data[key]);
        return m;
      }
      function swapArray(arr, index1, index2) {
        let oldObj = {};
        let newObj = {};
        let active = 0;
        arr.forEach((el, index) => {
          if (!el.id) {
            el.id = 'id' + el.timestamp;
          }
          data.list.forEach((item, j) => {
            if (el.id == item.id) {
              el.timestamp = item.num;
            }
          });
        });
        return arr;
      }
      if (data.oldIndex != undefined) {
        sortArr = JSON.parse(JSON.stringify(swapArray(newArr, data.newIndex, data.oldIndex)));
      } else {
        // 组件默认配置：script setup 组件经聚合层挂在组件对象上的 getDefaultConfig 工厂
        // （timestamp 依赖 num，需以 element 上已写入的 num 调用；原 Vue2 为 data().defaultConfig）
        let defaultConf;
        if (typeof data.element.getDefaultConfig === 'function') {
          defaultConf = data.element.getDefaultConfig(data.element.num);
        } else if (typeof data.element.data === 'function') {
          defaultConf = data.element.data().defaultConfig;
        } else {
          defaultConf = data.element.defaultConfig;
        }
        newArr.splice(data.newIndex, 0, defaultConf);
        sortArr = JSON.parse(JSON.stringify(swapArray(newArr, 0, 0)));
      }
      for (let i = 0; i < sortArr.length; i++) {
        newObj[sortArr[i].timestamp] = sortArr[i];
      }
      defaultArray.value = Object.assign({}, newObj);
    }
    /**
     * @description 更新数组某一组数据
     * @param {Object} data
     */
    function UPDATEARR(data) {
      for (var k in defaultArray.value) {
        if (defaultArray.value[k].id == data.val.id) {
          defaultArray.value[k] = data.val;
        }
      }
      let value = Object.assign({}, defaultArray.value);
      defaultArray.value = value;
    }
    /**
     * @description 保存组件名称
     * @param {string} name
     */
    function SETCONFIGNAME(name) {
      configName.value = name;
    }
    /**
     * @description 默认组件清空
     * @param {string} name
     */
    function SETEMPTY(name) {
      defaultArray.value = {};
    }
    function UPTITLE(val) {
      pageTitle.value = val;
    }
    function UPNAME(val) {
      pageName.value = val;
    }
    function UPSHOW(val) {
      pageShow.value = val;
    }
    function UPCOLOR(val) {
      pageColor.value = val;
    }
    function UPPIC(val) {
      pagePic.value = val;
    }
    function UPPICKER(val) {
      pageColorPicker.value = val;
    }
    function UPRADIO(val) {
      pageTabVal.value = val;
    }
    function UPPICURL(val) {
      pagePicUrl.value = val;
    }
    /**
     * @description 更新foot菜单配置
     * @param {Object} data
     */
    function footUpdata(data) {
      pageFooter.value.menuList = [];
      pageFooter.value.menuList = data;
    }
    /**
     * @description 更新foot自定义开关（原 mutation 体本身为空）
     * @param {Object} data
     */
    function footStatus(data) {}
    // 更新导航类型
    function footType(data) {
      pageFooter.value.navConfig.tabVal = data;
    }
    //底部导航下边距；
    function footBottom(data) {
      pageFooter.value.mbConfig.val = data;
    }
    /**
     * @description 更新foot配置
     * @param {Object} data
     */
    function footPageUpdata(data) {
      pageFooter.value = data;
    }
    /**
     * @description 更新bottomMenu配置
     * @param {Object} data
     */
    function bottomMenuUpdata(data) {
      bottomMenu.value = data;
    }
    function RESET_BOTTOM_MENU() {
      bottomMenu.value = JSON.parse(JSON.stringify(defaultBottomMenu));
    }
    /**
     * @description 更新title配置
     * @param {Object} data
     */
    function titleUpdata(data) {
      pageTitle.value = data;
    }
    /**
     * @description 更新name配置
     * @param {Object} data
     */
    function nameUpdata(data) {
      pageName.value = data;
    }
    //
    function showUpdata(data) {
      pageShow.value = data;
    }
    function colorUpdata(data) {
      pageColor.value = data;
    }
    function picUpdata(data) {
      pagePic.value = data;
    }
    function pickerUpdata(data) {
      pageColorPicker.value = data;
    }
    function radioUpdata(data) {
      pageTabVal.value = data;
    }
    function picurlUpdata(data) {
      pagePicUrl.value = data;
    }

    function getData(data) {}

    return {
      configName,
      pageTitle,
      pageName,
      pageShow,
      pageColor,
      pagePic,
      pageColorPicker,
      pageTabVal,
      pagePicUrl,
      defaultArray,
      bottomMenu,
      pageFooter,
      FOOTER,
      UPBOTTOMMENU,
      ADDARRAY,
      DELETEARRAY,
      ARRAYREAST,
      defaultArraySort,
      UPDATEARR,
      SETCONFIGNAME,
      SETEMPTY,
      UPTITLE,
      UPNAME,
      UPSHOW,
      UPCOLOR,
      UPPIC,
      UPPICKER,
      UPRADIO,
      UPPICURL,
      footUpdata,
      footStatus,
      footType,
      footBottom,
      footPageUpdata,
      bottomMenuUpdata,
      RESET_BOTTOM_MENU,
      titleUpdata,
      nameUpdata,
      showUpdata,
      colorUpdata,
      picUpdata,
      pickerUpdata,
      radioUpdata,
      picurlUpdata,
      getData,
    };
  },
  { persist: true },
);
