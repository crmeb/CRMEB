//app.js
var app = getApp();
// var wxh = require('../../utils/wxh.js');
App({
  onLaunch: function () {
    // 展示本地存储能力
    var that = this;
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)
    that.getRoutineStyle();
  },
  globalData: {
    routineStyle:'#ffffff',
    uid: null,
    openPages:'',
    spid:0,
    urlImages: '',
    url: 'https://shop.crmeb.net/'
  },
  getRoutineStyle:function(){
    var that = this;
    wx.request({
      url: that.globalData.url + '/routine/login/get_routine_style',
      method: 'post',
      dataType  : 'json',
      success: function (res) {
        that.globalData.routineStyle = res.data.data.routine_style;
        that.setBarColor();
      }
    })
  },
  setBarColor:function(){
    var that = this;
    wx.setNavigationBarColor({
      frontColor: '#000000',
      backgroundColor: that.globalData.routineStyle,
    })
  },
  setUserInfo : function(){
    var that = this;
    if (that.globalData.uid == null) {//是否存在用户信息，如果不存在跳转到首页123456
      wx.showToast({
        title: '用户信息获取失败!',
        icon: 'none',
        duration: 1500,
      })
      setTimeout(function () {
        wx.navigateTo({
          url: '/pages/load/load',
        })
      }, 1500)
    }
  },
})