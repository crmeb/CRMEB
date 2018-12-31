// pages/order-confirm/order-confirm.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    cartArr: [
      { "name": "微信", "icon": "icon-weixinzhifu", value:'weixin'},
      { "name": "余额支付", "icon": "icon-yuezhifu", value: 'yue' },
      // { "name": "线下付款", "icon": "icon-wallet", value: 'offline' },
      // { "name": "到店自提", "icon": "icon-dianpu", value: 'ziti'  },
    ],
    cartInfo : [],
    cartId : '',
    priceGroup :[],
    orderKey:'',
    seckillId:0,
    BargainId:0,
    combinationId:0,
    pinkId:0,
    offlinePostage : 0, 
    integralRatio: 1,
    usableCoupon : [], 
    userInfo : [],
    url: app.globalData.urlImages,
    addressId:0,
    couponId:0,
    couponPrice:'',
    couponInfo:[],
    addressInfo:[],
    mark:'',
    payType:'weixin',
    useIntegral:''
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var that = this;
    if (options.pinkId){
      that.setData({
        pinkId: options.pinkId
      })
    }
    if (options.addressId){
      that.setData({
        addressId: options.addressId
      });
    }
    if (options.couponId) {
      that.setData({
        couponId: options.couponId
      });
    }
    if (options.id == ''){
      wx.showToast({
        title: '请选择要购买的商品',
        icon: 'none',
        duration: 1000,
      })
      setTimeout(function(){
        that.toBuy();
      },1100)
    }else{
      this.getConfirm(options.id);
    } 
    that.getaddressInfo();
    that.getCouponRope();
  },
  bindHideKeyboard:function(e){
     this.setData({
       mark: e.detail.value
     })
  },
  radioChange:function(e){
    this.setData({
      payType: e.currentTarget.dataset.value
    })
  },
  subOrder:function(e){
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    if (that.data.payType == ''){
      wx.showToast({
        title: '请选择支付方式',
        icon: 'none',
        duration: 1000,
      })
    } else if (!that.data.addressId){
      wx.showToast({
        title: '请选择收货地址',
        icon: 'none',
        duration: 1000,
      })
    } else {
      wx.request({
        url: app.globalData.url + '/routine/auth_api/create_order?uid=' + app.globalData.uid +'&key='+ that.data.orderKey,
        method: 'POST',
        header: header,
        data: {
          addressId: that.data.addressId,
          formId: e.detail.formId,
          couponId: that.data.couponId,
          payType: that.data.payType,
          useIntegral: that.data.useIntegral,
          bargainId: that.data.BargainId,
          combinationId: that.data.combinationId,
          pinkId: that.data.pinkId,
          seckill_id: that.data.seckillId,
          mark: that.data.mark
        },
        success: function (res) {
          var data = res.data.data;
          if (res.data.code == 200 && res.data.data.status == 'SUCCESS'){
            wx.showToast({
              title: res.data.msg,
              icon: 'success',
              duration: 1000,
            })
            setTimeout(function () {
              wx.navigateTo({
                url: '/pages/orders-con/orders-con?order_id=' + data.result.orderId
              })
            }, 1200)
          } else if (res.data.code == 200 && res.data.data.status == 'ORDER_EXIST') {
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 1000,
            })
            setTimeout(function () {
              wx.navigateTo({
                url: '/pages/orders-con/orders-con?order_id=' + data.result.orderId
              })
            }, 1200)
          } else if (res.data.code == 200 && res.data.data.status == 'ORDER_EXIST') {
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 1000,
            })
            setTimeout(function () {
              wx.navigateTo({
                url: '/pages/orders-con/orders-con?order_id=' + data.result.orderId
              })
            }, 1200)
          }else if (res.data.code == 200 && res.data.data.status == 'EXTEND_ORDER'){
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 1000,
            })
            setTimeout(function () {
              wx.navigateTo({
                url: '/pages/orders-con/orders-con?order_id=' + data.result.orderId
              })
            }, 1200)
          } else if (res.data.code == 200 && res.data.data.status == 'WECHAT_PAY'){
            var jsConfig = res.data.data.result.jsConfig;
            wx.requestPayment({
              timeStamp: jsConfig.timeStamp,
              nonceStr: jsConfig.nonceStr,
              package: jsConfig.package,
              signType: jsConfig.signType,
              paySign: jsConfig.paySign,
              success: function(res) {
                wx.showToast({
                  title: '支付成功',
                  icon: 'success',
                  duration: 1000,
                })
                setTimeout(function () {
                  wx.navigateTo({
                    url: '/pages/orders-con/orders-con?order_id=' + data.result.orderId
                  })
                }, 1200)
              },
              fail: function(res) {
                wx.showToast({
                  title: '支付失败',
                  icon: 'none',
                  duration: 1000,
                })
                setTimeout(function () {
                  wx.navigateTo({
                    url: '/pages/orders-con/orders-con?order_id='+data.result.orderId
                  })
                }, 1200)
              },
              complete: function(res) {
                if (res.errMsg == 'requestPayment:cancel') {
                  wx.showToast({
                    title: '取消支付',
                    icon: 'none',
                    duration: 1000,
                  })
                setTimeout(function () {
                  wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
                    url: '/pages/orders-con/orders-con?order_id=' + data.result.orderId
                  })
                }, 1200)
                }
              },
            })
          }else{
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 1000,
            })
          }
        }
      })  
    }
  },
  getCouponRope:function(){
      var that = this;
      if (that.data.couponId){
        wx.request({
          url: app.globalData.url + '/routine/auth_api/get_coupon_rope?uid=' + app.globalData.uid,
          method: 'GET',
          data: {
            couponId: that.data.couponId
          },
          success: function (res) {
            if (res.data.code == 200) {
              that.setData({
                couponInfo: res.data.data,
                couponPrice: '-' + res.data.data.coupon_price
              })
            }else{
              that.setData({
                couponInfo: [],
                couponPrice: ''
              })
            }
          }
        })
      }
  },
  getaddressInfo:function(){
    var that = this;
      if (that.data.addressId){
        wx.request({
          url: app.globalData.url + '/routine/auth_api/get_user_address?uid=' + app.globalData.uid,
          method: 'GET',
          data:{
            addressId: that.data.addressId
          },
          success: function (res) {
            if (res.data.code == 200) {
                that.setData({
                  addressInfo:res.data.data
                })
            }
          }
        })
      }else{
        wx.request({
          url: app.globalData.url + '/routine/auth_api/user_default_address?uid=' + app.globalData.uid,
          method: 'GET',
          success: function (res) {
            if (res.data.code == 200) {
              that.setData({
                addressInfo: res.data.data,
                addressId: res.data.data.id
              })
            }
          }
        })
      }
  },
  getAddress: function () {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded'
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_address_list?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
          wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
            url: '/pages/address/address?cartId=' + that.data.cartId + '&pinkId=' + that.data.pinkId + '&couponId=' + that.data.couponId
          })
        }
      }
    })
  },
  getCoupon:function(){
    var that = this;
    wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
      url: '/pages/coupon-status/coupon-status?cartId=' + that.data.cartId + '&totalPrice=' + that.data.priceGroup.totalPrice + '&pinkId=' + that.data.pinkId + '&addressId=' + that.data.addressId
    })
  },
  toBuy:function(){
    wx.switchTab({
      url: '/pages/buycar/buycar'
    });
  },
  toAddress: function () {
    var that = this;
    wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
      url: '/pages/addaddress/addaddress?cartId=' + that.data.cartId + '&pinkId=' + that.data.pinkId + '&couponId=' + that.data.couponId
    })
  },
  getConfirm: function (cartIdsStr){
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/confirm_order?uid=' + app.globalData.uid,
      method: 'POST',
      data: {
        cartId: cartIdsStr
      },
      header: header,
      success: function (res) {
        if(res.data.code == 200){
          that.setData({
            userInfo: res.data.data.userInfo,
            cartInfo: res.data.data.cartInfo,
            integralRatio: res.data.data.integralRatio,
            offlinePostage: res.data.data.offlinePostage,
            orderKey: res.data.data.orderKey, 
            priceGroup: res.data.data.priceGroup,
            cartId: res.data.data.cartId,
            seckillId: res.data.data.seckill_id,
            usableCoupon: res.data.data.usableCoupon
          })
          that.getBargainId();
        }
      }
    })
  },
  getBargainId:function(){
    var that = this;
    var cartINfo = that.data.cartInfo;
    var BargainId = 0;
    var combinationId = 0;
    cartINfo.forEach(function (value, index, cartINfo){
      BargainId = cartINfo[index].bargain_id,
      combinationId = cartINfo[index].combination_id
    })
    that.setData({
      BargainId: BargainId,
      combinationId:combinationId
    })
    console.log(that.data.BargainId);
    console.log(that.data.seckillId);
    console.log(that.data.combinationId);
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
  
  }
})