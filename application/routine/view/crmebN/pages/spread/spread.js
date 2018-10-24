var app = getApp();
var wxh = require('../../utils/wxh.js');
// pages/spread/spread.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    code:'',
    userinfo:[],
    cont:'',
    first:0,
    limit:8,
    mainArray:[],
    hiddens:true
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
    this.getCode();
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    this.money(header);
    this.spread(header);
    this.userbill(header);
  },
  money: function (header){
    var that = this;
  wx.request({
    url: app.globalData.url + '/routine/auth_api/my?uid=' + app.globalData.uid,
    method: 'POST',
    header: header,
    success: function (res) {
      if (res.data.code==200){
        that.setData({
          userinfo: res.data.data
        })
      }else{
      that.setData({
        userinfo: []
      })
      }
    }
  });
},
  getCode:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_code?uid=' + app.globalData.uid,
      method: 'POST',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            code:res.data.msg
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
        console.log(that.data.code);
      }
    });
  },
  spread: function (header){
  var that = this;
  wx.request({
    url: app.globalData.url + '/routine/auth_api/get_spread_list?uid=' + app.globalData.uid,
    method: 'POST',
    header: header,
    success: function (res) {
      if (res.data.code == 200) {
      that.setData({
        cont: res.data.data.count       
      })
      }else{
        that.setData({
          cont: 0
        })
      }
    }
  });
},
  userbill: function (header){
    var that = this;
    that.setData({
      first:0
    });
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_balance_list?uid=' + app.globalData.uid,
      method: 'GET',
      data:{
        first: that.data.first,
        limit: that.data.limit,
      },
      success: function (res) {
        // console.log(res.data.data);
        if (res.data.code == 200) {
        that.setData({
          mainArray: res.data.data
        })
        }else{
          that.setData({
            mainArray:[]
          })
        }
      }
    })
},
  cash:function(){
    wx.navigateTo({
      url: '../../pages/cash/cash?money='+this.data.userinfo.now_money,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
  porder:function(){
    wx.navigateTo({
      url: '../../pages/promotion-order/promotion-order',
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },
  erm:function(e){
      this.setData({
        hiddens:false
      })
      this.getCode();
  },
  tanguan:function(e){
    this.setData({
      hiddens: true
    })
  },
  onReachBottom: function (p) {
    var that = this;
    var limit = that.data.limit;
    var first = that.data.first;
    if (!first) first = first+1;
    var firstS = first * limit;
    var mainArray = that.data.mainArray;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_balance_list?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        first: firstS,
        limit: that.data.limit,
      },
      success: function (res) {
        if (res.data.code == 200) {
          for (var i = 0; i < res.data.data.length; i++) {
            mainArray.push(res.data.data[i])
          }
          that.setData({
            first: first + 1,
            mainArray: mainArray
          });
        } else {
        }
      }
    })
  },
})