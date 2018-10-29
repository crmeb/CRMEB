// pages/promotion-card/promotion-card.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.url,
    code:'',
    userinfo: app.globalData
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/my?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        console.log(res)
        if (res.data.code == 200) {

          that.setData({
            userinfo: res.data.data
          })
        } else {
          that.setData({
            userinfo: []
          })
        }
      }
    });  
    that.getCode();
  },
  getCode:function(){
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_code?uid=' + app.globalData.uid,
      method: 'get',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            code: res.data.msg
          })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 0
          })
          that.setData({
            code: ''
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