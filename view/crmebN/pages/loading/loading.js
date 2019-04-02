var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    logo: '',
    name: '',
    url: app.globalData.url,
    is_login:true,
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.auth();
  },
  //授权判断
  auth: function () {
    var that = this; 
    wx.checkSession({
      success: function (res) {
        wx.getSetting({
          success(res) {
            //第一次弹窗允许授权
            if (!res.authSetting['scope.userInfo'] || !wx.getStorageSync('session_key')) {
              that.showlogin(false, 2500);
            } else {
              //第二次直接调用获取用户信息
              that.getUserInfoBydecryptCode();
            }
          }
        })
      },
      fail: function (res) {
        that.showlogin(false,2500);
      }
    })
    
  },
  //首次点击允许获取用户信息并且授权
  getUserInfo: function (e) {
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
              that.showlogin(false);
              //解密获取用户信息
              that.getUserInfoBydecryptCode();
            },
            fail: function () {
              that.showlogin(false);
            },
          })
        } else {
          that.showlogin(false);
          console.log('登录失败！' + res.errMsg)
        }
      }
    })
  },
  //解密获取用户信息
  getUserInfoBydecryptCode: function (e) {
    var that = this;
    wx.getUserInfo({
      lang: 'zh_CN',
      success: function (res) {
        // console.log(res);//第二次获取用户信息获得iv和encryptedData
        var pdata = res;
        pdata.userInfo = res.userInfo;
        pdata.spid = app.globalData.spid;//获取推广人ID
        pdata.spreadid = app.globalData.spreadid;//获取推广人ID 2.5.36
        if (res.iv) {
          pdata.iv = encodeURI(res.iv);
          pdata.encryptedData = res.encryptedData;
          pdata.session_key = wx.getStorageSync('session_key');//获取上一步获取的session_key
          wx.request({
            url: app.globalData.url + '/routine/logins/index',
            method: 'post',
            dataType  : 'json',
            data: {
              info: pdata
            },
            success: function (res) {
              console.log(res);
              
              if (res.data.data == 'ILLEGAL_BUFFER'){
                wx.setStorageSync('session_key','');
                that.auth();
              }else{
                app.globalData.uid = res.data.data.uid;
                if (!app.globalData.uid || app.globalData.uid == 'undefined') {
                  that.showlogin(true);
                } else {
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
              }
            }
              
          })
        } else {
          console.log('登录失败！' + res.errMsg)
        }
      },
      fail: function () {
      },
    })
  },
  //是否显示登录授权框
  showlogin:function(ishouw,times = 1500){
    var that = this;
    setTimeout(function () {
      that.setData({
        is_login: ishouw
      })
    }, times)
  }
})