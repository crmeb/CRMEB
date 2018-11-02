var app = getApp();
var wxh = require('../../utils/wxh.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    nowstatus:"",
    orderlist:[],
    search: "",
    first:0,
    title: "玩命加载中...",
    hidden: false
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
    if (parseInt(options.nowstatus)){
      this.setData({
        nowstatus: (parseInt(options.nowstatus) - 1).toString()
      })
      this.getorderlist(parseInt(options.nowstatus) - 1);
    } else {
      this.getorderlist("");
    }
  },
  getorderlist:function(e){
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var that = this;
    var search = that.data.search;
    var limit = 4;
    var first = that.data.first;
    var startpage = limit * first;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_order_list?uid='+app.globalData.uid,
      data: { type: e, search: search, first: startpage, limit: limit },
      method: 'get',
      header: header,
      success: function (res) {
       
        var $data = res.data.data;
        var len = $data.length;
        for (var i = 0; i < len; i++) {
          that.data.orderlist.push($data[i]);
        }
        that.setData({
          first: first + 1,
          orderlist: that.data.orderlist
        });
        console.log(that.data.orderlist)
        if (len < limit) {
          that.setData({
            title: "数据已经加载完成",
            hidden: true
          });
          return false;
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
  statusClick:function(e){
    var nowstatus = e.currentTarget.dataset.show;
    this.setData({
       nowstatus: nowstatus,
       orderlist: [],
       first:0,
       title: "玩命加载中...",
       hidden: false
    });
    this.getorderlist(nowstatus);
  },
  searchSubmit:function(){
    this.setData({
      orderlist: [],
      first: 0,
      title: "玩命加载中...",
      hidden: false
    });
    var e = this.data.nowstatus;
    this.getorderlist(e);
  },
  searchInput:function(e){
    this.setData({
      search: e.detail.value
    });
  },
  delOrder:function(e){
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var uni = e.currentTarget.dataset.uni;
    var that = this;
    wx.showModal({
      title: '确认删除订单？',
      content: '订单删除后将无法查看',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.url + '/routine/auth_api/user_remove_order?uid=' + app.globalData.uid,
            data: { uni: uni },
            method: 'get',
            header: header,
            success: function (res) {
              wx.hideLoading();
              if (res.data.code == 200){
                wx.showToast({
                  title: '成功',
                  icon: 'success',
                  duration: 2000
                });
              } else {
                wx.showToast({
                  title: res.data.msg,
                  icon: 'none',
                  duration: 2000
                })
              }
              
            },
            fail: function (res) {
              console.log('submit fail');
            },
            complete: function (res) {
              console.log('submit complete');
            }
          });
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  },
  onReachBottom: function () {
    var e = this.data.nowstatus;
    this.getorderlist(e);
  }
})