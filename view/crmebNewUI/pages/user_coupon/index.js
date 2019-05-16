// pages/coupon-list/index.js
const app=getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '我的优惠券',
      'color': false
    },
    couponsList:[],
    loading:false,
  },

  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.getUseCoupons();
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 获取领取优惠券列表
  */
  getUseCoupons:function(){
    var that = this;
    app.baseGet(app.U({ c: 'coupons_api', a:'get_use_coupons'}),function(res){
      that.setData({ loading: true, couponsList:res.data});
    });
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },


})