var app = getApp();
Page({
  data: {
    logo: '',
    name: '',
    spid: 0,
    url: app.globalData.url,
  },
  onLoad: function (options) {
    var that = this;
    that.getEnterLogo();
    app.setBarColor();
    if (options.scene) that.data.spid = options.scene;
  },
  getEnterLogo: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/login/get_enter_logo',
      method: 'post',
      dataType  : 'json',
      success: function (res) {
        that.setData({
          logo: res.data.data.site_logo,
          name: res.data.data.site_name
        })
      }
    })
  },
  //获取用户信息并且授权
  getUserInfo: function(e){
    var userInfo = e.detail.userInfo;
    userInfo.spid = this.data.spid;
    wx.login({
      success: function (res) {
        if (res.code) {
          userInfo.code = res.code;
          wx.request({
            url: app.globalData.url + '/routine/login/index',
            method: 'post',
            dataType  : 'json',
            data: {
              info: userInfo
            },
            success: function (res) {
              app.globalData.uid = res.data.data.uid;
              if (app.globalData.openPages != '' && app.globalData.openPages != undefined) {//跳转到指定页面
                wx.navigateTo({
                  url: app.globalData.openPages
                })
              } else {//跳转到首页
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
  },
})