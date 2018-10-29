// pages/pay-error/pay-error.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    phone:'13399182577'
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.getSiteServicePhone();
  },

  goContact:function(){
    var that = this;
    wx.makePhoneCall({
      phoneNumber: that.data.phone,
    })
  },

  getSiteServicePhone:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/login/get_site_service_phone',
      method: 'post',
      dataType  : 'json',
      success: function (res) {
        that.setData({
          phone: res.data.data.site_service_phone
        });
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