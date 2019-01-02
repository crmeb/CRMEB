// pages/address/address.js
var app = getApp();
Page({
  data: {
    _num:'',
    cartId: '',
    pinkId: '',
    couponId: '',
    addressArray:[]
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    if (options.cartId) {
      this.setData({
        cartId: options.cartId,
        pinkId: options.pinkId,
        couponId: options.couponId,
      })
    }
    this.getAddress();
  },
  getWxAddress:function(){
    var that = this;
     wx.authorize({
       scope: 'scope.address',
       success: function(res) {
         wx.chooseAddress({
           success: function(res) {
             console.log(res);
             var addressP = {};
             addressP.province = res.provinceName;
             addressP.city = res.cityName;
             addressP.district = res.countyName;
             wx.request({
               url: app.globalData.url + '/routine/auth_api/edit_user_address?uid=' + app.globalData.uid + '&openid=' + app.globalData.openid,
               method: 'POST',
               data: {
                 address: addressP,
                 is_default: 1,
                 real_name: res.userName,
                 post_code: res.postalCode,
                 phone: res.telNumber,
                 detail: res.detailInfo,
                 id: 0
               },
               success: function (res) {
                 if (res.data.code == 200) {
                   wx.showToast({
                     title: '添加成功',
                     icon: 'success',
                     duration: 1000
                   })
                   that.getAddress();
                 }
               }
             })
           },
           fail: function(res) {
             if (res.errMsg == 'chooseAddress:cancel'){
               wx.showToast({
                 title: '取消选择',
                 icon: 'none',
                 duration: 1500
               })
             }
           },
           complete: function(res) {},
         })
       },
       fail: function(res) {
         console.log(res);
       },
       complete: function(res) {},
     })
  },
  getAddress: function () {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_address_list?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            addressArray: res.data.data
          })
          for (var i in res.data.data) {
            if (res.data.data[i].is_default) {
              that.setData({
                _num: res.data.data[i].id
              })
            }
          }
        }
      }
    })
  },
  addAddress:function(){

    var cartId = this.data.cartId;
    var pinkId = this.data.pinkId;
    var couponId = this.data.couponId;
    this.setData({
      cartId: '',
      pinkId:'',
      couponId:'',
    })
    wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
      url: '/pages/addaddress/addaddress?cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId
    })
  },
  goOrder:function(e){
    var id = e.currentTarget.dataset.id;
    var cartId = '';
    var pinkId = '';
    var couponId = '';
    if (this.data.cartId && id){
      cartId = this.data.cartId; 
      pinkId = this.data.pinkId;
      couponId = this.data.couponId;
      this.setData({
        cartId : '',
        pinkId : '',
        couponId : '',
      })
      wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
        url: '/pages/order-confirm/order-confirm?id=' + cartId + '&addressId=' + id + '&pinkId=' + pinkId + '&couponId=' + couponId
      })
    }
  },
  delAddress:function(e){
    var id = e.currentTarget.dataset.id;
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/remove_user_address?uid=' + app.globalData.uid,
      method: 'GET',
      header: header,
      data:{
        addressId:id
      },
      success: function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: '删除成功',
            icon: 'success',
            duration: 1000,
          })
          that.getAddress();
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 1000,
          })
        }
      }
    })
  },
  editAddress: function (e) {
    var cartId = this.data.cartId;
    var pinkId = this.data.pinkId;
    var couponId = this.data.couponId;
    this.setData({
      cartId: '',
      pinkId: '',
      couponId: '',
    })
    wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
      url: '/pages/addaddress/addaddress?id=' + e.currentTarget.dataset.id + '&cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId
    })
  },
  activetap:function(e){
    var id = e.target.dataset.idx;
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/set_user_default_address?uid=' + app.globalData.uid,
      method: 'GET',
      header: header,
      data:{
        addressId:id
      },
      success: function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: '设置成功',
            icon: 'success',
            duration: 1000,
          })
          that.setData({
            _num: id
          })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 1000,
          })
        }
      }
    })
  }
})