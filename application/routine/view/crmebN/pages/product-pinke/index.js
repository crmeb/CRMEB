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
    num: 1,
    description:"",
    minusStatus: 'disabled',  
    storeInfo: [],
    replyCount:0,
    reply:[],
    pinking:[],
    pindAll:[],
    indicatorDots: true,//是否显示面板指示点;
    autoplay: true,//是否自动播放;
    interval: 3000,//动画间隔的时间;
    duration: 500,//动画播放的时长;
    indicatorColor: "rgba(51, 51, 51, .3)",
    indicatorActivecolor: "#ffffff",
    show :false,
    combinationId:0,
    collect:false,//收藏
    CartCount:0,//购物车数量
    prostatus:false,
    timeer: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options){
    app.setBarColor();
    if (options.id){
      this.setData({
        combinationId: options.id
      })
      this.getCombinationDetail();
    }else{
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000,
      })
      setTimeout(function(){
         wx.navigateTo({
           url: '/pages/pink-list/index',
         })
      },1200)
    }
  },
  setTime: function () {//到期时间戳
    var that = this;
    var newTime = new Date().getTime() / 1000;
    var endTimeList = that.data.pinking;
    var countDownArr = [];
    for (var i in endTimeList) {
      var endTime = endTimeList[i].stop_time;
      var obj = [];
      if (endTime - newTime > 0) {
        var time = endTime - newTime;
        var day = parseInt(time / (60 * 60 * 24));
        var hou = parseInt(time % (60 * 60 * 24) / 3600);
        var min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
        var sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
        hou = parseInt(hou) + parseInt(day * 24);
        obj = {
          day: that.timeFormat(day),
          hou: that.timeFormat(hou),
          min: that.timeFormat(min),
          sec: that.timeFormat(sec)
        }
      } else {
        obj = {
          day: '00',
          hou: '00',
          min: '00',
          sec: '00'
        }
        that.getList();
      }
      endTimeList[i].time = obj;
    }
    that.setData({
      pinking: endTimeList
    })
    var timeer = setTimeout(that.setTime, 1000);
    that.setData({
      timeer: timeer
    })
  },
  timeFormat(param) {//小于10的格式化函数
    return param < 10 ? '0' + param : param;
  },
  getCombinationDetail:function(){
     var that = this;
     if (!that.data.combinationId){
       wx.showToast({
         title: '参数错误',
         icon: 'none',
         duration: 1000,
       })
       setTimeout(function () {
         wx.navigateTo({
           url: '/pages/pink-list/index',
         })
       }, 1200)
     }
     wx.request({
       url: app.globalData.url + '/routine/auth_api/combination_detail?uid=' + app.globalData.uid,
       data: {
         id: that.data.combinationId
       },
       method: 'GET',
       dataType: 'json',
       success: function(res) {
         if(res.data.code == 200){
           that.setData({
             storeInfo: res.data.data.storeInfo,
             description: res.data.data.storeInfo.description, 
             replyCount: res.data.data.replyCount,
             reply: res.data.data.reply,
             pinking: res.data.data.pink,
             pindAll: res.data.data.pindAll,
             collect:res.data.data.storeInfo.userCollect
           })
         }
         that.setTime();
         WxParse.wxParse('description', 'html', that.data.description, that, 0);
       }
     })
  },
  showList: function(){
    this.setData({
      show:true
    })
  },
  close: function(){
    this.setData({
      show: false
    })
  },
  goPink:function(e){
     console.log(e);
     var pinkId = e.currentTarget.dataset.id;
     wx.navigateTo({
       url: '/pages/join-pink/index?id=' + pinkId,
     })
  },
  parameterShow: function(e){
    var that = this;
    var pinkId = e.detail.value.pinkId;
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
        productId: that.data.storeInfo.product_id,
        cartNum: that.data.num,
        uniqueId: '',
        combinationId: that.data.combinationId,
        secKillId: 0
      },
      success: function (res) {
        if (res.data.code == 200) {
          if (pinkId) {
            wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
              url: '/pages/order-confirm/order-confirm?id=' + res.data.data.cartId + '&pinkId=' + pinkId
            })
          } else {
            wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
              url: '/pages/order-confirm/order-confirm?id=' + res.data.data.cartId
            })
          }
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
  modelbg:function(e){
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
  setCollect: function () {
    if (this.data.collect) this.unCollectProduct();
    else this.collectProduct();
  },
  unCollectProduct: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/uncollect_product?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        productId: that.data.combinationId,
        category:'pink_product'
      },
      success: function (res) {
        wx.showToast({
          title: '取消收藏成功',
          icon: 'success',
          duration: 1500,
        })
        that.setData({
          collect: false,
        })
      }
    })
  },
  collectProduct: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/collect_product?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        productId: that.data.combinationId,
        category: 'pink_product'
      },
      success: function (res) {
        wx.showToast({
          title: '收藏成功',
          icon: 'success',
          duration: 1500,
        })
        that.setData({
          collect: true,
        })
      }
    })
  },
  getCar: function () {
    wx.switchTab({
      url: '/pages/buycar/buycar'
    });
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
})