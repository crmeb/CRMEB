// pages/integral-con/integral-con.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo:[],
    integralInfo:[],
    first:0,
    limit:8,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
     this.getUserInfo();
    this.getList();
  },//user_integral_list
  getList:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_integral_list?uid=' + app.globalData.uid,
      method: 'GET',
      data:{
        first: that.data.first,
        limit: that.data.limit,
      },
      success: function (res) {
        that.setData({
          integralInfo: res.data.data,
        })
      }
    });
  },
  getUserInfo:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/my?uid=' + app.globalData.uid,
      method: 'POST',
      success: function (res) {
        that.setData({
          userInfo: res.data.data,
        })
        console.log(that.data.userInfo);
      }
    });
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
    var that = this;
    var first = that.data.first;
    var limit = that.data.limit;
    if (!first) first = 1;
    var startpage = limit * first;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_integral_list?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        first: startpage,
        limit: that.data.limit,
      },
      success: function (res) {
        var len = res.data.data.length;
        for (var i = 0; i < len; i++) {
          that.data.integralInfo.push(res.data.data[i])
        }
        that.setData({
          first: first + 1,
          integralInfo: that.data.integralInfo
        });
      }
    });
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})