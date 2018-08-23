// pages/cut-one/cut-one.js
var app = getApp();
var wxh = require('../../utils/wxh.js');
var WxParse = require('../../wxParse/wxParse.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    uid: app.globalData.uid,//登录人uid
    bargainUid:0,//参与砍价uid
    bargainUserInfo:[],//参与人信息
    bargainId:0,//砍价产品ID
    status:0,// 0未参与砍价  1参与砍价 2 帮别人砍价 3 已帮别人砍价  4 砍价结束
    count:[],//顶部人数
    bargainUserHelp:[],//砍价帮人
    HelpCount:0,//砍价帮总人数
    HelpPrice:0,//剩余多少金额
    pricePercent:0,//进度条
    bargainUserHelpUserInfo:[],
    url: app.globalData.urlImages,
    hiddens: true,
    hiddens2:true,
    product:[],
    countDownDay:'00',
    countDownHour:'00',
    countDownMinute:'00',
    countDownSecond:'00',
  },
  onLoad: function (options) {
    app.setBarColor();
    app.globalData.openPages = '/pages/cut-one/cut-one?bargainUid=' + options.bargainUid + '&id=' + options.id;
    app.setUserInfo();
    if (options.id && options.bargainUid) {
      this.setData({
        bargainId: options.id,
        bargainUid: options.bargainUid
      })
      this.getbargain();
      this.getBargainCount();
      this.addLookBargain();
    } else if (options.id){
      this.setData({
        bargainId: options.id,
        bargainUid: app.globalData.uid
      })
      this.getbargain();
      this.getBargainCount();
      this.addLookBargain();
    }else {
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000
      })
      setTimeout(function () {
        wx.navigateTo({
          url: '/pages/cut-list/cut-list'
        })
      }, 1200)
    }
  },//获取砍价产品
  getbargain: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_bargain?uid=' + app.globalData.uid,
      data: { bargainId: that.data.bargainId },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            product: res.data.data
          })
          that.dtime();
          if (that.data.bargainUid == app.globalData.uid) {
            that.setData({
              status: 1
            })
            that.setBargain();
          } else {
            that.setData({
              status: 2
            })
            that.getBargainUser();
            that.getUserInfo();
            that.getBargainHelpCount();
          }
          if (that.data.product.description) WxParse.wxParse('description', 'html', that.data.product.description, that, 0); 
          if (that.data.product.rule) WxParse.wxParse('rule', 'html', that.data.product.rule, that, 0);
        } else {
          that.setData({
            product: []
          })
        }
      }
    })
  },//倒计时
  dtime: function () {
    var that = this;
    var timeStamp = that.data.product.stop_time;
    wxh.time2(timeStamp, that);
  },//获取顶部人数
  getBargainCount: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_bargain_count?uid=' + app.globalData.uid,
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            count: res.data.data
          })
        } else {
          that.setData({
            count: []
          })
        }
      }
    })
  },//参与砍价
  setBargain: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/set_bargain?uid=' + app.globalData.uid,
      data: { bargainId: that.data.bargainId },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: res.data.msg,
            icon: 'success',
            duration: 2000
          })
          that.setData({
            status:1,
            bargainUid: app.globalData.uid
          })
        } else {
          // wx.showToast({
          //   title: res.data.msg,
          //   icon: 'none',
          //   duration: 2000
          // })
        }
        that.setBargainHelp();
        that.isBargainUser();
      }
    })
  },//参与砍价帮砍
  setBargainHelp: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/set_bargain_help?uid=' + app.globalData.uid,
      data: { 
        bargainId: that.data.bargainId,
        bargainUserId: that.data.bargainUid
      },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200){
          wx.showToast({
            title: res.data.msg,
            icon: 'success',
            duration: 2000
          })
          if (that.data.bargainUid != app.globalData.uid) {
            that.setData({
              status: 3,
              bargainUserHelpUserInfo: res.data.data,
              hiddens2: false
            })
          }else{
            that.setData({
              status: 1,
              bargainUserHelpUserInfo: res.data.data,
              hiddens2: false
            })
          }
          that.getBargainUser();
          that.getUserInfo();
          that.getBargainHelpCount();
        } else {
          that.isBargainUserHelp();
        }
      }
    })
  },//判断当前用户是否可以砍价
  isBargainUserHelp:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/is_bargain_user_help?uid=' + app.globalData.uid,
      data: { 
        bargainId: that.data.bargainId,
        bargainUserId: that.data.bargainUid
        },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
        } else {
          if (that.data.bargainUid != app.globalData.uid) {
            wx.showToast({
              title: '您不能在帮' + that.data.bargainUserInfo.nickname + '砍价了',
              icon: 'none',
              duration: 2000
            })
            that.setData({
              status: 3
            })
          } else {
            that.setData({
              status: 1,
            })
          }
        }
      }
    })
  },
  //判断当前登录人是否参与砍价
  isBargainUser: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/is_bargain_user?uid=' + app.globalData.uid,
      data: { bargainId: that.data.bargainId },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            bargainUid: app.globalData.uid,
            status: 1
          })
          that.getBargainUser();
          that.getUserInfo();
          that.getBargainHelpCount();
        } else {
          that.setData({
            bargainUid: 0,
            status:0
          })
        }
      }
    })
  },//获取砍价帮
  getBargainUser: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_bargain_user?uid=' + app.globalData.uid,
      data: { 
        bargainId: that.data.bargainId,
        bargainUid: that.data.bargainUid
      },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            bargainUserHelp: res.data.data
          })
        } else {
          that.setData({
            bargainUserHelp: []
          })
        }
      }
    })
  },//获取当前参与砍价人的信息
  getUserInfo:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_info_uid?uid=' + app.globalData.uid,
      data: {
        userId: that.data.bargainUid
      },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            bargainUserInfo: res.data.data
          })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
          that.setData({
            bargainUserInfo: []
          })
        }
      }
    })
  },//获取砍价帮总人数
  getBargainHelpCount:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_bargain_help_count?uid=' + app.globalData.uid,
      data: {
        bargainId: that.data.bargainId,
        bargainUserId: that.data.bargainUid
      },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          if (res.data.data.price <= 0 && app.globalData.uid == that.data.bargainUid){
            that.setData({
               status:4
            })
          }
          that.setData({
            HelpCount: res.data.data.count,
            HelpPrice: res.data.data.price,
            pricePercent: res.data.data.pricePercent
          })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
          that.setData({
            HelpCount: 0,
            HelpPrice:0,
            pricePercent:0
          })
        }
      }
    })
  },
  getCutList:function(){
    wx.navigateTo({
      url: '/pages/cut-list/cut-list'
    })
  },//砍价成功后支付订单
  goOrderPay:function(){
    var that = this;
    if (that.data.bargainUid != app.globalData.uid){
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 2000
      })
    }else{
      wx.request({
        url: app.globalData.url + '/routine/auth_api/now_buy?uid=' + app.globalData.uid,
        method: 'GET',
        data: {
          productId: that.data.product.product_id,
          cartNum: that.data.product.num,
          bargainId: that.data.product.id,
        },
        success: function (res) {
          if (res.data.code == 200) {
            wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
              url: '/pages/order-confirm/order-confirm?id=' + res.data.data.cartId
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
    }
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },
  rules:function(e){
      this.setData({
        hiddens:false
      })
  },
  butguan:function(e){
    this.setData({
      hiddens: true
    })
  },
  butguan2:function(e){
    this.setData({
      hiddens2: true
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
  onReachBottom: function () {
  
  },

  addLookBargain: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/add_look_bargain?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        bargainId: that.data.bargainId,
      },
      success: function (res) {
        that.getBargainCount();
      }
    })
  },
  addShareBargain:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/add_share_bargain?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        bargainId: that.data.bargainId,
      },
      success: function (res) {
        that.getBargainCount();
      }
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    var that = this;
    return {
      title: that.data.bargainUserInfo.nickname+'邀请您帮砍价',
      path: '/pages/cut-one/cut-one?bargainUid=' + that.data.bargainUid + '&id=' + that.data.bargainId,
      imageUrl: that.data.url + that.data.product.image,
      success:function(){
        that.addShareBargain();
        wx.showToast({
          title: '分享成功',
          icon: 'success',
          duration: 2000
        })
      }
    }
  }
})