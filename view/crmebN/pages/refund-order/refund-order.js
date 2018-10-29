// pages/refund-order/refund-order.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    search:'',
    url: app.globalData.urlImages,
    first:0,
    orderType:'-3',
    orderlist:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.getorderlist();
  },
  searchSubmit: function () {
    this.setData({
      orderlist: [],
      first: 0,
    });
    var e = this.data.nowstatus;
    this.getorderlist(e);
  },
  searchInput: function (e) {
    this.setData({
      search: e.detail.value
    });
  },
  getorderlist: function () {
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var that = this;
    var search = that.data.search;
    var limit = 8;
    var orderType = that.data.orderType;
    var first = that.data.first;
    var startpage = limit * first;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_order_list?uid=' + app.globalData.uid,
      data: { type: orderType, search: search, first: startpage, limit: limit },
      method: 'get',
      header: header,
      success: function (res) {
        var $data = res.data.data;
        if (!startpage && !$data.length){
            wx.showToast({
              title: '暂无订单',
              icon: 'none',
              duration: 2000
            })
        } else {
          var len = $data.length;
          for (var i = 0; i < len; i++) {
            that.data.orderlist.push($data[i]);
          }
          that.setData({
            first: first + 1,
            orderlist: that.data.orderlist
          });
        }
      },
      fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {
        console.log('submit complete');
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
    this.getorderlist();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})