var app = getApp();
var Util = require('../../utils/util.js')
Page({
  /**
   * 页面的初始数据
   */
  data: {
    imgUrls: [],
    itemNew: [],
    activityList: [],
    menus: [],
    bastBanner: [],
    bastInfo: '',
    bastList: [],
    fastInfo: '',
    fastList: [],
    firstInfo: '',
    firstList: [],
    salesInfo: '',
    likeInfo: [],
    lovelyBanner: [],
    benefit: [],
    indicatorDots: false,
    circular: true,
    autoplay: true,
    interval: 3000,
    duration: 500,
    parameter: {
      'navbar': '0',
      'return': '0'
    },
    window: false,
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (options.spid) app.globalData.spid = options.spid;
    if (options.scene) app.globalData.code = decodeURIComponent(options.scene);
  },
  catchTouchMove: function (res) {
    return false
  },
  onColse: function () {
    this.setData({ window: false });
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getIndexConfig();
    if (app.globalData.isLog && app.globalData.token) this.get_issue_coupon_list();
  },
  get_issue_coupon_list: function () {
    var that = this;
    app.baseGet(app.U({ c: 'coupons_api', a: 'get_issue_coupon_list', q: { limit: 3 } }), function (res) {
      that.setData({ couponList: res.data });
      if (!res.data.length) that.setData({ window: false });
    });
  },
  getIndexConfig: function () {
    var that = this;
    var url = app.U({ c: 'public_api', a: 'index' }, app.globalData.url);
    app.baseGet(url, function (res) {
      that.setData({
        imgUrls: res.data.banner,
        menus: res.data.menus,
        itemNew: res.data.roll,
        activityList: res.data.activity,
        bastBanner: res.data.info.bastBanner,
        bastInfo: res.data.info.bastInfo,
        bastList: res.data.info.bastList,
        fastInfo: res.data.info.fastInfo,
        fastList: res.data.info.fastList,
        firstInfo: res.data.info.firstInfo,
        firstList: res.data.info.firstList,
        salesInfo: res.data.info.salesInfo,
        likeInfo: res.data.likeInfo,
        lovelyBanner: res.data.info,
        benefit: res.data.benefit,
        logoUrl: res.data.logoUrl,
        couponList: res.data.couponList,
      });
      wx.getSetting({
        success(res) {
          if (!res.authSetting['scope.userInfo']) {
            that.setData({ window: that.data.couponList.length ? true : false });
          } else {
            that.setData({ window: false });
          }
        }
      });
    });
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({ window: false });
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
    this.getIndexConfig();
    if (app.globalData.isLog && app.globalData.token) this.get_issue_coupon_list();
    wx.stopPullDownRefresh();
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

  }
})