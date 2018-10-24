// pages/mall/payment/payment.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userinfo:[],
    now_money:0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.getUserInfo();
  },
  getUserInfo:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_info?uid=' + app.globalData.uid,
      method: 'GET',
      success: function (res) {
        that.setData({
          userinfo: res.data.data,
          now_money: res.data.data.now_money
        })
      }
    })
  },
  submitSub:function(e){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_form_id?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        formId: e.detail.formId
      },
      success: function (res) { }
    })
    if (!e.detail.value.number) {
      wx.showToast({
        title: '请输入充值金额',
        icon: 'none',
        duration: 1000,
      })
      return false;
    }
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_wechat_recharge?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        price: e.detail.value.number
      },
      success: function (res) {
        var jsConfig = res.data.data;
        if (res.data.code == 200) {
          wx.requestPayment({
            timeStamp: jsConfig.timeStamp,
            nonceStr: jsConfig.nonceStr,
            package: jsConfig.package,
            signType: jsConfig.signType,
            paySign: jsConfig.paySign,
            success: function (res) {
              wx.showToast({
                title: '支付成功',
                icon: 'success',
                duration: 1000,
              })
              that.setData({
                now_money: parseInt(that.data.now_money) + parseInt(e.detail.value.number)
              });
              setTimeout(function () {
                wx.navigateTo({
                  url: '/pages/main/main?now=' + that.data.now_money + '&uid=' + app.globalData.uid,
                })
              }, 1200)
            },
            fail: function (res) {
              wx.showToast({
                title: '支付失败',
                icon: 'success',
                duration: 1000,
              })
            },
            complete: function (res) {
              if (res.errMsg == 'requestPayment:cancel') {
                wx.showToast({
                  title: '取消支付',
                  icon: 'none',
                  duration: 1000,
                })
              }
            },
          })
        }else{
          wx.showToast({
            title: '支付失败',
            icon: 'none',
            duration: 1000,
          })
        }
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