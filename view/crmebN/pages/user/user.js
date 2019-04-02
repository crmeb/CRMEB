var app = getApp();
var wxh = require('../../utils/wxh.js');
// pages/user/user.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
  url: app.globalData.urlImages,
  userinfo:[],
  orderStatusNum:[],
  coupon:'',
  collect:''
  },

  setTouchMove: function (e) {
    var that = this;
    wxh.home(that, e);
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/my?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        that.setData({
          userinfo: res.data.data,
          orderStatusNum: res.data.data.orderStatusNum
        })
      }
    });
  },
  goNotification:function(){
      wx.navigateTo({
        url: '/pages/news-list/news-list',
      })
  },
  onShow: function () {
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/my?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        that.setData({
          userinfo: res.data.data
        })
      }
    });
  },  
   /**
   * 生命周期函数--我的余额
   */
  money:function(){
    wx.navigateTo({
      url: '/pages/main/main?now=' + this.data.userinfo.now_money + '&uid='+app.globalData.uid,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
   /**
   * 生命周期函数--我的积分
   */
  integral: function () {
    wx.navigateTo({
      url: '/pages/integral-con/integral-con?inte=' + this.data.userinfo.integral + '&uid=' + app.globalData.uid,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
   /**
   * 生命周期函数--我的优惠卷
   */
  coupons: function () {
    wx.navigateTo({
      url: '/pages/coupon/coupon',
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
   /**
   * 生命周期函数--我的收藏
   */
  collects: function () {
    wx.navigateTo({
      url: '/pages/collect/collect',
    })
  },
   /**
   * 生命周期函数--我的推广人
   */
  extension:function(){
    wx.navigateTo({
      url: '/pages/feree/feree',
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
   /**
   * 生命周期函数--我的推广
   */
  myextension: function () {
    wx.navigateTo({
      url: '/pages/extension/extension',
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
  getPhoneNumber: function (res){
    var that = this;
    if (res.detail.errMsg == "getPhoneNumber:ok"){
        var pdata = {};
        pdata.iv = encodeURI(res.detail.iv);
        pdata.encryptedData = res.detail.encryptedData;
        pdata.session_key = wx.getStorageSync('session_key');//获取上一步获取的session_key
        wx.request({
          url: app.globalData.url + '/routine/auth_api/bind_mobile?uid=' + app.globalData.uid,
          method: 'post',
          dataType  : 'json',
          data: {
            info: pdata
          },
          success: function (res) {
            if(res.data.code == 200){
              wx.showToast({
                title: '绑定成功',
                icon: 'success',
                duration: 2000
              })
              that.setData({
                ['userinfo.phone'] : true
              })

            }else{
              wx.showToast({
                title: '绑定失败',
                icon: 'none',
                duration: 2000
              })
            }
          },
        })
    } else {
      wx.showToast({
        title: '取消授权',
        icon: 'none',
        duration: 2000
      })
    }
  }
   /**
   * 生命周期函数--我的砍价
   */
  // cut_down_the_price:function(){
  //   wx.navigateTo({
  //     url: '../../pages/feree/feree',
  //     success: function (res) { },
  //     fail: function (res) { },
  //     complete: function (res) { },
  //   })
  // }
})