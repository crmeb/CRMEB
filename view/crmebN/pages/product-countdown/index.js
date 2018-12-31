// pages/product-con/index.js
var app = getApp();
var wxh = require('../../utils/wxh.js');
var WxParse = require('../../wxParse/wxParse.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    indicatorDots: true,//是否显示面板指示点;
    autoplay: true,//是否自动播放;
    interval: 3000,//动画间隔的时间;
    duration: 500,//动画播放的时长;
    indicatorColor: "rgba(51, 51, 51, .3)",
    indicatorActivecolor: "#ffffff",
    countDownHour: "00",
    countDownMinute: "00",
    countDownSecond: "00",
    reply:[],
    replyCount:0,
    collect:false,
    SeckillList:[],
    CartCount: 0,
    seckillId:0
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    if (options.id){
      this.setData({
        seckillId: options.id
      })
      this.getSeckillDetail();
    }else{
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000
      })
      setTimeout(function(){
        wx.navigateTo({
          url: '/pages/miao-list/miao-list?'
        })
      },1200)
    }
    this.getCartCount();
  }, 
  getCartCount: function () {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_cart_num?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        that.setData({
          CartCount: res.data.data
        })
      }
    })
  },
  getCar: function () {
    wx.switchTab({
      url: '/pages/buycar/buycar'
    });
  },
  goCoupon: function () {
    wx.navigateTo({
      url: "/pages/coupon-status/coupon-status"
    })
  },
  getSeckillDetail:function(){
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/seckill_detail?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      data:{
        id: that.data.seckillId
      },
      success: function (res) {
        console.log(res);
        that.setData({
          SeckillList: res.data.data.storeInfo,
          replyCount: res.data.data.replyCount,
          reply: res.data.data.reply
        });
        var timeStamp = that.data.SeckillList.stop_time;
        wxh.time(timeStamp, that);
        WxParse.wxParse('description', 'html', that.data.SeckillList.description, that, 0);
      }
    })
  },
  parameterShow: function (e) {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_form_id?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        formId: e.detail.formId
      },
      success: function (res) { }
    })
    wx.request({
      url: app.globalData.url + '/routine/auth_api/now_buy?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        productId: that.data.SeckillList.product_id,
        cartNum: that.data.num,
        uniqueId: '',
        combinationId:0,
        secKillId: that.data.seckillId
      },
      success: function (res) {
        if (res.data.code == 200) {
          wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
            url: '/pages/order-confirm/order-confirm?id=' + res.data.data.cartId
          })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
        }
      }
    })
  },
  modelbg: function (e) {
    this.setData({
      prostatus: false
    })
  },
  bindMinus: function () {
    var that = this;
    wxh.carmin(that)
  },
  bindPlus: function () {
    var that = this;
    wxh.carjia(that);
  },
  tapsize: function (e) {
    console.log("123");
    var that = this;
    wxh.tapsize(that, e);
  },
  tapcolor: function (e) {
    var that = this;
    wxh.tapcolor(that, e);
  },
  setCollect: function () {
    if (this.data.collect) this.unCollectProduct();
    else this.collectProduct();
  },
  unCollectProduct: function () {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/uncollect_product?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      data: {
        productId: that.data.SeckillList.product_id,
        category: 'product_seckill'
      },
      success: function (res) {
        that.setData({
          collect: false,
        })
      }
    })
  },
  collectProduct: function () {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/collect_product?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      data: {
        productId: that.data.SeckillList.product_id,
        category:'product_seckill'
      },
      success: function (res) {
        that.setData({
          collect: true,
        })
      }
    })
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

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

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

  }
})