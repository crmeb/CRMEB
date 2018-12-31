// pages/coupon/coupon.js
// var fileData = require('../../utils/util.js'); //暴露的接口；
var app = getApp();
Page({
  data: {
    _active:0,
    headerArray: ['可领取'],
    couponArray:"",
    userstatus:"",
    title: '加载中...',
    cartId:'',
    pinkId:'',
    addressId:'',
    totalPrice:''
  },
  headertap:function(e){
      this.setData({
        _active: e.target.dataset.idx
      });
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    if (options.cartId){
      this.setData({
        cartId: options.cartId,
        totalPrice: options.totalPrice,
        pinkId: options.pinkId,
        addressId: options.addressId,
      })
      this.getCouponOrderList();
    }else {
      this.getCouponList();
    }
  },
  getCouponUser:function(e){
    // e.currentTarget.dataset.id;
    var that = this;
    var id = e.currentTarget.dataset.id;
    if (that.data.cartId) {
      var cartId = that.data.cartId;
      var totalPrice = that.data.totalPrice;
      var addressId = that.data.addressId;
      var pinkId = that.data.pinkId;
      that.setData({
        cartId:'',
        totalPrice:'',
        addressId:'',
        pinkId:'',
      })
      wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
        url: '/pages/order-confirm/order-confirm?id=' + cartId + '&couponId=' + id + '&addressId=' + addressId + '&pinkId=' + pinkId
      })
    } else {
      var header = {
        'content-type': 'application/x-www-form-urlencoded'
      };
      wx.request({
        url: app.globalData.url + '/routine/auth_api/user_get_coupon?uid=' + app.globalData.uid,
        method: 'POST',
        header: header,
        data: {
          couponId: id
        },
        success: function (res) {
          var array = that.data.couponArray;
          if (res.data.code == 200) {
            for (var i in array) {
              if (array[i].id == id) {
                array[i].is_use = true;
              }
            }
            that.setData({
              couponArray: array
            })
            wx.showToast({
              title: '领取成功',
              icon: 'success',
              duration: 1500
            })
          } else {
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 1500
            })
          }
        }
      })
    }
  },
  getCouponOrderList:function(){
    var that = this;
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_use_coupon_order?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        totalPrice: that.data.totalPrice
      },
      success: function (res) {
        that.setData({
          userstatus: '立即使用',
          couponArray: res.data.data,
          title: "没有数据了",
          loadinghidden: true
        })
      }
    })
  },
  getCouponList:function(){
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded'
    };
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_issue_coupon_list?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      data: {
        limit: 10
      },
      success: function (res) {
        that.setData({
          userstatus: '立即领取',
          couponArray: res.data.data,
          title: "没有数据了",
          loadinghidden: true
        })
      }
    })
  }
})