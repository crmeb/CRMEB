// pages/snapUpDetails/index.js
var app = getApp();
const wxh = require('../../../utils/wxh.js');
const wxParse = require('../../../wxParse/wxParse.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    id: 0,
    time:0,
    countDownHour: "00",
    countDownMinute: "00",
    countDownSecond: "00",
    storeInfo:[],
    imgUrls: [],
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '抢购详情页',
      'color': false
    },
    attribute: {
      'cartAttr': false
    },
    productSelect: [],
    productAttr: [],
    productValue: [],
    isOpen: false,
    attr: '请选择',
    attrValue: '',
  },

  onLoadFun:function(){
    this.getSeckillDetail();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (options.hasOwnProperty('id') && options.hasOwnProperty('time')) {
      this.setData({ id: options.id, time: options.time });
      app.globalData.openPages = '/pages/activity/goods_seckill_details/index?id=' + this.data.id + '&time=' + this.data.time;
    }else{
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000,
        mask: true,
      });
      setTimeout(function(){ wx.navigateBack({ delta: 1 }) },1200)};
  },
  onMyEvent: function (e) {
    this.setData({ 'attribute.cartAttr': e.detail.window, isOpen: false })
  },
  /**
   * 购物车数量加和数量减
   * 
  */
  ChangeCartNum: function (e) {
    //是否 加|减
    var changeValue = e.detail;
    //获取当前变动属性
    var productSelect = this.data.productValue[this.data.attrValue];
    //如果没有属性,赋值给商品默认库存
    if (productSelect === undefined && !this.data.productAttr.length) productSelect = this.data.productSelect;
    //不存在不加数量
    if (productSelect === undefined) return;
    //提取库存
    var stock = productSelect.stock || 0;
    //设置默认数据
    if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
    //数量+
    if (changeValue) {
      productSelect.cart_num++;
      //大于库存时,等于库存
      if (productSelect.cart_num > this.data.storeInfo.num) productSelect.cart_num = this.data.storeInfo.num;
      this.setData({
        ['productSelect.cart_num']: productSelect.cart_num,
        cart_num: productSelect.cart_num,
        ['productSelect.is_on']: productSelect.cart_num > this.data.storeInfo.num,
      });
    } else {
      //数量减
      productSelect.cart_num--;
      //小于1时,等于1
      if (productSelect.cart_num < 1) productSelect.cart_num = 1;
      this.setData({
        ['productSelect.cart_num']: productSelect.cart_num,
        cart_num: productSelect.cart_num,
        ['productSelect.is_on']:false,
      });
    }
  },
  /**
   * 属性变动赋值
   * 
  */
  ChangeAttr: function (e) {
    var values = e.detail;
    var productSelect = this.data.productValue[values];
    var storeInfo = this.data.storeInfo;
    if (productSelect) {
      this.setData({
        ["productSelect.image"]: productSelect.image,
        ["productSelect.price"]: productSelect.price,
        ["productSelect.stock"]: productSelect.stock,
        ['productSelect.unique']: productSelect.unique,
        ['productSelect.cart_num']: 1,
        ['productSelect.is_on']: productSelect.cart_num > this.data.storeInfo.num,
        attrValue: values,
        attr: '已选择'
      });
    } else {
      this.setData({
        ["productSelect.image"]: storeInfo.image,
        ["productSelect.price"]: storeInfo.price,
        ["productSelect.stock"]: 0,
        ['productSelect.unique']: '',
        ['productSelect.cart_num']: 0,
        ['productSelect.is_on']:false,
        attrValue: '',
        attr: '请选择'
      });
    }
  },
  selecAttr: function () {
    this.setData({
      'attribute.cartAttr': true
    })
  },
  /*
 *  下订单
 */
  goCat: function () {
    var that = this;
    console.log(that.data.productValue);
    var productSelect = this.data.productValue[this.data.attrValue];
    //打开属性
    if (this.data.isOpen)
      this.setData({ 'attribute.cartAttr': true })
    else
      this.setData({ 'attribute.cartAttr': !this.data.attribute.cartAttr });
    //只有关闭属性弹窗时进行加入购物车
    if (this.data.attribute.cartAttr === true && this.data.isOpen == false) return this.setData({ isOpen: true });
    //如果有属性,没有选择,提示用户选择
    console.log(this.data.productAttr.length);
    if (this.data.productAttr.length && productSelect === undefined && this.data.isOpen == true) return app.Tips({ title: '请选择属性' });
    app.baseGet(app.U({
      c: 'auth_api',
      a: 'now_buy',
      q: {
        productId: that.data.storeInfo.product_id,
        secKillId: that.data.id,
        bargainId: 0,
        combinationId: 0,
        cartNum: that.data.cart_num,
        uniqueId: productSelect !== undefined ? productSelect.unique : '',
        is_new:1
      }
    }), function (res) {
      that.setData({ isOpen: false });
      wx.navigateTo({ url: '/pages/order_confirm/index?cartId=' + res.data.cartId });
    })
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    if(this.data.isClone && app.globalData.isLog) this.getSeckillDetail();
  },
  getSeckillDetail: function () {
    var that = this;
    var url = app.U({ c: 'seckill_api', a: 'seckill_detail' }, app.globalData.url);
    app.basePost(url, { id: that.data.id }, function (res) {
      var title = res.data.storeInfo.title;
      that.setData({
        ["parameter.title"]: title.length > 10 ? title.substring(0, 10)+'...' : title,
        storeInfo: res.data.storeInfo,
        imgUrls: res.data.storeInfo.images
      });
      that.setProductSelect();
      app.globalData.openPages = '/pages/activity/goods_seckill_details/index?id=' + that.data.id + '&time=' + that.data.time + '&scene=' + that.data.storeInfo.uid;
      wxParse.wxParse('description', 'html', that.data.storeInfo.description || '', that, 0);
      wxh.time(that.data.time, that);
    }, function (res) {
      wx.showToast({
        title: res.msg,
        icon: 'none',
        duration: 1000,
        mask: true,
      });
    });
  },
  setProductSelect:function(){
    var that = this;
    if (that.data.productSelect.length == 0){
      that.setData({
        ['productSelect.image']: that.data.storeInfo.image,
        ['productSelect.store_name']: that.data.storeInfo.title,
        ['productSelect.price']: that.data.storeInfo.price,
        ['productSelect.stock']: that.data.storeInfo.stock,
        ['productSelect.unique']: '',
        ['productSelect.cart_num']: 1,
        ['productSelect.is_on']: that.data.storeInfo.num <= 1,
      })
    }
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({isClone:true});
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    var that = this;
    return {
      title: that.data.storeInfo.title,
      path: app.globalData.openPages,
      imageUrl: that.data.storeInfo.image,
      success: function () {
        wx.showToast({
          title: '分享成功',
          icon: 'success',
          duration: 2000
        })
      }
    }
  }
})