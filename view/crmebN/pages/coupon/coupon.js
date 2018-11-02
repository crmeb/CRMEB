var app = getApp();
// pages/coupon/coupon.js
// var fileData = require('../../utils/util.js'); //暴露的接口；
Page({
  data: {
    url: app.globalData.urlImages,
    _active:0,
    headerArray: ['全部', '未使用', '已使用','已过期'],
    couponArray:"",
    userstatus:"",
    title:"正在加载中..."
   
  },
  headertaps:function(e){
      this.setData({
        _active: e.target.dataset.idx
      });
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var that = this;
    //flag = 1{0表示正常，1未使用，2已使用，3已过期，4新增券}
    var flag = this.data._active;
    setTimeout(function(){
      wx.request({
        url: app.globalData.url + '/routine/auth_api/get_use_coupons?uid=' + app.globalData.uid,
        data:{types:0},
        method: 'GET',
        header: header,
        success: function (res) {  
          if (res.data.code==200){
            that.setData({
              couponArray: res.data.data,
              title: "没有数据了",
              loadinghidden: true
            })
          } else{  
          that.setData({
            couponArray: [],
            title: "没有数据了",
            loadinghidden: false
          })
          }
        }
      })
    },1000)
  },
  headertap:function(e){
    var that = this;
    // console.log(e);
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
     var $type=e.target.dataset.idx
     wx.request({
       url: app.globalData.url + '/routine/auth_api/get_use_coupons?uid=' + app.globalData.uid,
       data: {types:$type},
       method: 'GET',
       header: header,
       success: function (res) {
         if (res.data.code==200){
           that.setData({
             couponArray: res.data.data,
             title: "没有数据了",
             loadinghidden: true,
             _active: $type
           })
         }else{
         that.setData({
           couponArray: [],
           title: "没有数据了",
           loadinghidden: false,
           _active: ''
         })
         }
       }
     })
  }



})