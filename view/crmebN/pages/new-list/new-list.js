// pages/new-list/new-list.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    swiperCurrent: 0,
    indicatorDots: false,
    interval: 5000,
    duration: 1000,
    first:0,
    limit:8,
    newList:[],
    bannerList: [],
    hotList: [],
    title: '加载中···',
    hidden: true,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.getNewList();
    this.getArticleBanner();
    this.getArticleHot();
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
  getArticleBanner: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_article_banner?uid=' + app.globalData.uid,
      method: 'GET',
      success: function (res) {
        that.setData({
          bannerList: res.data.data
        })
      }
    })
  },
  getArticleHot:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_article_hot?uid=' + app.globalData.uid,
      method: 'GET',
      success: function (res) {
        that.setData({
          hotList: res.data.data
        })
      }
    })
  },
  getNewList:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_cid_article?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        cid : 0,
        first: that.data.first,
        limit: that.data.limit
      },
      success: function (res) {
        that.setData({
          newList:res.data.data
        })
      }
    })
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
  onReachBottom: function (p) {
    var that = this;
    that.setData({
      hidden: false,
    })
    var limit = that.data.limit;
    var offset = that.data.first;
    if (!offset) offset = 1;
    var startpage = limit * offset;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_cid_article?uid=' + app.globalData.uid,
      data: { cid:0,limit: limit, first: startpage },
      method: 'GET',
      success: function (res) {
        var len = res.data.data.length; 
        for (var i = 0; i < len; i++) {
          that.data.newList.push(res.data.data[i])
        }
        that.setData({
          first: offset + 1,
          newList: that.data.newList
        })
        if (len < limit) {
          that.setData({
            title: "数据已经加载完成",
          });
        }
      },
      fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {
        console.log('submit complete');
      }
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})