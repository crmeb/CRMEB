// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * diy自定义组件
 * */
export default {
  namespaced: true,
  state: {
    configName: '',
    pageTitle: '',
    pageName: '',
    pageShow: 1,
    pageColor: 0,
    pagePic: 0,
    pageColorPicker: '#f5f5f5',
    pageTabVal: 0,
    pagePicUrl: '',
    // 已知组件列表默认数据 数组
    defaultArray: {},
    pageFooter: {
      name: 'pageFoot',
      setUp: {
        tabVal: 0,
      },
      status: {
        title: '是否自定义',
        name: 'status',
        status: false,
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

      menuList: [
        {
          imgList: [require('@/assets/images/foot-001.png'), require('@/assets/images/foot-002.png')],
          name: '首页',
          link: '/pages/index/index',
        },
        {
          imgList: [require('@/assets/images/foot-003.png'), require('@/assets/images/foot-004.png')],
          name: '分类',
          link: '/pages/goods_cate/goods_cate',
        },
        {
          imgList: [require('@/assets/images/foot-005.png'), require('@/assets/images/foot-006.png')],
          name: '购物车',
          link: '/pages/order_addcart/order_addcart',
        },
        {
          imgList: [require('@/assets/images/foot-007.png'), require('@/assets/images/foot-008.png')],
          name: '我的',
          link: '/pages/user/index',
        },
      ],
    },
  },
  mutations: {
    FOOTER(state, data) {
      state.pageFooter.status.title = data.title;
      state.pageFooter.menuList[2] = data.name;
    },
    /**
     * @description 默认配置push到数组里面
     * @param {Object} state vuex state
     * @param {Object} data
     * 把默认数据添加到默认数组里面，解耦重复组件公用一条配置的问题
     */
    ADDARRAY(state, data) {
      data.val.id = 'id' + data.val.timestamp;
      state.defaultArray[data.num] = data.val;
    },
    /**
     * @description 删除列表第几个默认数据
     * @param {Object} state vuex state
     * @param {Object} data 数据
     */
    DELETEARRAY(state, data) {
      let tempObj = delete state.defaultArray[data.num];
    },
    /**
     * @description 删除列表第几个默认数据
     * @param {Object} state vuex state
     * @param {Object} data 数据
     */
    ARRAYREAST(state, data) {
      let tempObj = delete state.defaultArray[data];
    },
    /**
     * @description 数组排序
     * @param {Object} state vuex state
     * @param {Object} data 位置index记录
     */
    defaultArraySort(state, data) {
      let newArr = objToArr(state.defaultArray);
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
        // if(index2>index1){
        //     arr[index2].timestamp = (parseInt(arr[index1].timestamp) -1).toString()
        // }else{
        //     arr[index2].timestamp = (parseInt(arr[index1].timestamp) +1).toString()
        // }

        // arr[index1] = arr.splice(index2, 1, arr[index1])[0];
        return arr;
      }
      if (data.oldIndex != undefined) {
        sortArr = JSON.parse(JSON.stringify(swapArray(newArr, data.newIndex, data.oldIndex)));
      } else {
        newArr.splice(data.newIndex, 0, data.element.data().defaultConfig);
        sortArr = JSON.parse(JSON.stringify(swapArray(newArr, 0, 0)));
      }
      for (let i = 0; i < sortArr.length; i++) {
        newObj[sortArr[i].timestamp] = sortArr[i];
      }
      state.defaultArray = Object.assign({}, newObj);
    },
    /**
     * @description 更新数组某一组数据
     * @param {Object} state vuex state
     * @param {Object} data
     */
    UPDATEARR(state, data) {
      for (var k in state.defaultArray) {
        if (state.defaultArray[k].id == data.val.id) {
          state.defaultArray[k] = data.val;
        }
      }
      let value = Object.assign({}, state.defaultArray);
      state.defaultArray = value;
    },
    /**
     * @description 保存组件名称
     * @param {Object} state vuex state
     * @param {string} data
     */
    SETCONFIGNAME(state, name) {
      state.configName = name;
    },
    /**
     * @description 默认组件清空
     * @param {Object} state vuex state
     * @param {string} data
     */
    SETEMPTY(state, name) {
      state.defaultArray = {};
    },
    UPTITLE(state, val) {
      state.pageTitle = val;
    },
    UPNAME(state, val) {
      state.pageName = val;
    },
    UPSHOW(state, val) {
      state.pageShow = val;
    },
    UPCOLOR(state, val) {
      state.pageColor = val;
    },
    UPPIC(state, val) {
      state.pagePic = val;
    },
    UPPICKER(state, val) {
      state.pageColorPicker = val;
    },
    UPRADIO(state, val) {
      state.pageTabVal = val;
    },
    UPPICURL(state, val) {
      state.pagePicUrl = val;
    },
    /**
     * @description 更新foot菜单配置
     * @param {Object} state vuex state
     * @param {string} data
     */
    footUpdata(state, data) {
      state.pageFooter.menuList = [];
      state.pageFooter.menuList = data;
    },
    /**
     * @description 更新foot自定义开关
     * @param {Object} state vuex state
     * @param {string} data
     */
    footStatus(state, data) {
      state.pageFooter.status.status = data;
    },
    /**
     * @description 更新foot配置
     * @param {Object} state vuex state
     * @param {string} data
     */
    footPageUpdata(state, data) {
      state.pageFooter = data;
    },
    /**
     * @description 更新title配置
     * @param {Object} state vuex state
     * @param {string} data
     */
    titleUpdata(state, data) {
      state.pageTitle = data;
    },
    /**
     * @description 更新name配置
     * @param {Object} state vuex state
     * @param {string} data
     */
    nameUpdata(state, data) {
      state.pageName = data;
    },
    //
    showUpdata(state, data) {
      state.pageShow = data;
    },
    colorUpdata(state, data) {
      state.pageColor = data;
    },
    picUpdata(state, data) {
      state.pagePic = data;
    },
    pickerUpdata(state, data) {
      state.pageColorPicker = data;
    },
    radioUpdata(state, data) {
      state.pageTabVal = data;
    },
    picurlUpdata(state, data) {
      state.pagePicUrl = data;
    },
  },
  actions: {
    getData({ commit }, data) {},
  },
};
