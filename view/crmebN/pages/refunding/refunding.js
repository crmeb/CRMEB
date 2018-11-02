// pages/refunding/refunding.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    orderId:'',
    url: app.globalData.urlImages,
    ordercon:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    if (options.id){
        this.setData({
          orderId: options.id
        })
        this.getOrder();
    }else{
      that.isBool();
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },
  getOrder:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_order?uid=' + app.globalData.uid,
      data: { uni: that.data.orderId },
      method: 'get',
      success: function (res) {
        wx.hideLoading();
        if(res.data.code == 200){
          res.data.data.refund_reason_time = that.setTime(res.data.data.refund_reason_time);
          that.setData({
            ordercon: res.data.data
          });
        }else{
          that.isBool();
        }
        console.log(that.data.ordercon);
      },
      fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {
        console.log('submit complete');
      }
    });
  },
  setTime: function (timestamp3){
    var newDate = new Date();
    newDate.setTime(timestamp3 * 1000);
    return newDate.toLocaleString();
  },
  isBool:function(){
    wx.showToast({
      title: '参数错误',
      icon: 'none',
      duration: 1000
    })
    setTimeout(function () {
      wx.navigateTo({
        url: '/pages/refund-order/refund-order'
      })
    }, 1200)
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