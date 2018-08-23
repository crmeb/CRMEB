// pages/join-pink/index.js
var app = getApp();
var wxh = require('../../utils/wxh.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    pinkKId:0,
    pinkId:0,
    count:0,
    currentPinkOrder: '',
    isOk: 0,
    pinkAll: [],
    pinkBool: 0,
    pinkT: [],
    storeInfo: [],
    storeCombination: [],
    storeCombinationHost: [],
    userBool: 0,
    url: app.globalData.urlImages,
    countDownHour: "00",
    countDownMinute: "00",
    countDownSecond: "00"
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.globalData.openPages = '/pages/join-pink/index?id=' + options.id;
    app.setUserInfo();
    if (options.id){
      this.setData({
        pinkId: options.id
      })
      this.getPinkList();
    }else{
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000,
      })
      setTimeout(function(){
         wx.navigateBack({})
      },1200)
    }
  },
  goPinkOrder:function(e){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_form_id?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        formId: e.detail.formId
      },
      success: function (res) { }
    })
    wx.request({
      url: app.globalData.url + '/routine/auth_api/now_buy?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        productId: that.data.storeCombination.product_id,
        cartNum: that.data.pinkT.total_num,
        uniqueId: '',
        combinationId: that.data.storeCombination.id,
        secKillId: 0
      },
      success: function (res) {
        if (res.data.code == 200) {
            wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
              url: '/pages/order-confirm/order-confirm?id=' + res.data.data.cartId + '&pinkId=' + that.data.pinkT.id
            })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
        }
      }
    })
  },
  getPinkList:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_pink?uid=' + app.globalData.uid,
      data: {
        id: that.data.pinkId
      },
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        console.log(res);
        if(res.data.code == 200){
          that.setData({
            count: res.data.data.count,
            currentPinkOrder: res.data.data.current_pink_order,
            isOk: res.data.data.is_ok,
            pinkAll: res.data.data.pinkAll,
            pinkBool: res.data.data.pinkBool,
            pinkT: res.data.data.pinkT,
            storeInfo: res.data.data.storeInfo,
            storeCombination: res.data.data.store_combination,
            storeCombinationHost: res.data.data.store_combination_host,
            userBool: res.data.data.userBool
          })
          var timeStamp = that.data.pinkT.stop_time;
          wxh.time(timeStamp, that);
          console.log(that.data.pinkId);
          console.log(that.data.storeCombinationHost);
        }
      },
    })
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
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    var that = this;
    return {
      title: that.data.storeCombination.userInfo.nickname + '邀请您参团',
      path: '/pages/join-pink/index?id=' + that.data.pinkId,
      imageUrl: that.data.url + that.data.storeCombination.image,
      success: function () {
        wx.showToast({
          title: '分享成功',
          icon: 'success',
          duration: 2000
        })
      }
    }
  }
})