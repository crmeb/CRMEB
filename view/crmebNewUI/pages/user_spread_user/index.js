// pages/my-promotion/index.js
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '我的推广',
      'color': true,
      'class': '0'
    },
    userInfo:[],
    yesterdayPrice:0.00,
    isClone:false
  },
  onLoadFun:function(){
    this.getUserInfo();
    this.yesterdayCommission();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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
    if (app.globalData.isLog && this.data.isClone){
      this.getUserInfo();
      this.yesterdayCommission();
    }
  },
  /**
   * 获取个人用户信息
   */
  getUserInfo: function () {
    var that = this;
    app.baseGet(app.U({ c: 'user_api', a: 'my' }), function (res) {
      that.setData({ userInfo: res.data });
    });
  },
  yesterdayCommission: function () {
    var that = this;
    app.baseGet(app.U({ c: 'user_api', a: 'yesterday_commission' }), function (res) {
      that.setData({ yesterdayPrice: res.data });
    });
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({isClone:true});
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