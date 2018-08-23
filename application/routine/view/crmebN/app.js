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
    if (that.globalData.uid == null) {//是否存在用户信息，如果不存在跳转到首页
      wx.showToast({
        title: '用户信息获取失败',
        icon: 'none',
        duration: 1500,
      })
      setTimeout(function () {
        wx.navigateTo({
          url: '/pages/enter/enter',
        })
      }, 2000)
    }
  },
  getUserInfo: function () {
    var that = this;
    wx.getUserInfo({
      lang: 'zh_CN',
      success: function (res) {
        var userInfo = res.userInfo
        wx.login({
          success: function (res) {
            if (res.code) {
              userInfo.code = res.code;
              userInfo.spid = that.globalData.spid;
              wx.request({
                url: that.globalData.url + '/routine/login/index',
                method: 'post',
                dataType  : 'json',
                data: {
                  info: userInfo
                },
                success: function (res) {
                  that.globalData.uid = res.data.data.uid;
                  if (!res.data.data.status){
                    wx.redirectTo({
                      url: '/pages/login-status/login-status',
                    })
                  }
                  if (that.globalData.openPages != '') {
                    wx.reLaunch({
                      url: that.globalData.openPages
                    })
                  } else {
                    wx.switchTab({
                      url: '/pages/index/index'
                    })
                  }
                },
                fail: function () {
                  console.log('获取用户信息失败');
                  wx.navigateTo({
                    url: '/pages/enter/enter',
                  })
                },
              })
            } else {
              console.log('登录失败！' + res.errMsg)
            }
          },
          fail: function () {
            console.log('获取用户信息失败');
            wx.navigateTo({
              url: '/pages/enter/enter',
            })
          },
        })
      },
      fail:function(){
        console.log('获取用户信息失败');
        wx.navigateTo({
          url: '/pages/enter/enter',
        })
      },
    })
  },
  getUserInfoEnter: function () {
    var that = this;
    wx.getUserInfo({
      lang: 'zh_CN',
      success: function (res) {
        var userInfo = res.userInfo
        wx.login({
          success: function (res) {
            if (res.code) {
              userInfo.code = res.code;
              userInfo.spid = that.globalData.spid;
              wx.request({
                url: that.globalData.url + '/routine/login/index',
                method: 'post',
                dataType  : 'json',
                data: {
                  info: userInfo
                },
                success: function (res) {
                  that.globalData.uid = res.data.data.uid;
                  if (that.globalData.openPages != '') {
                    wx.reLaunch({
                      url: that.globalData.openPages
                    })
                  } else {
                    wx.reLaunch({
                      url: '/pages/index/index'
                    })
                  }
                }
              })
            } else {
              console.log('登录失败！' + res.errMsg)
            }
          }
        })
      }
    })
  },
})