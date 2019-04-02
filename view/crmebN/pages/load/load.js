var app = getApp();
Page({
  data: {
    logo: '',
    name: '',
    url: app.globalData.url,
    code:'',
    userInfo:'',
  },
  onLoad: function (options) {
    var that = this;
    app.setBarColor();
  },
  //首次点击允许获取用户信息并且授权
  getUserInfo: function(e){
    var that = this;
    var userInfo = e.detail.userInfo;
    userInfo.spid = app.globalData.spid;
    userInfo.spreadid = app.globalData.spreadid;//获取推广人ID 2.5.36
    wx.login({
      success: function (res) {
        // console.log(res);//获取code
        if (res.code) {
          userInfo.code = res.code;
          wx.request({
            url: app.globalData.url + '/routine/logins/setCode',
            method: 'post',
            dataType  : 'json',
            data: {
              info: userInfo
            },
            success: function (res) {
              // console.log(res);//根据code 获取openid session_key unionid(未试过用相关连应用无法获取unionid)
              wx.setStorageSync('session_key', res.data.session_key);//保存小程序缓存中
              wx.navigateTo({
                url: '/pages/loading/loading',
              })          
            }
          })
        } else {
          console.log('登录失败！' + res.errMsg)
        }
      }
    })
  }
})