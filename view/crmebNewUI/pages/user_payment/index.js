// pages/mall/payment/payment.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '余额充值',
      'color': false,
    },
    number:'',
    focus:true,
    userinfo:{},
    placeholder:"0.00",
  },

  /**
   * 登录授权回调
  */
  onLoadFun:function(){
    this.getUserInfo();
  },
  setPlaceholderStatus:function(event){
    if (event.detail.value.length == 0) this.setData({ placeholder: '0.00' });
  },
  setPlaceholder:function(){
    this.setData({ placeholder : '' })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  /**
   * 获取用户信息
  */
  getUserInfo:function(){
    var that = this;
    app.baseGet(app.U({ c: 'user_api', a:'get_my_user_info'}),function(res){  
      that.setData({ userinfo:res.data});
    });
  },
  /*
  * 用户充值
  */
  submitSub:function(e){
    var that = this;
    var value = e.detail.value.number;
    if (parseFloat(value) < 0){
      wx.showToast({
        title: '请输入金额',
        icon: 'none',
        duration: 1000,
        mask: true,
      });
      return false;
    };
    app.baseGet(app.U({ c: 'public_api', a: 'get_form_id', q: { formId: e.detail.formId}}),null,null,true);
    app.baseGet(app.U({ c: 'user_api', a: 'user_wechat_recharge', q: { price: value}}),function(res){
      var jsConfig = res.data;
      wx.requestPayment({
        timeStamp: jsConfig.timestamp,
        nonceStr: jsConfig.nonceStr,
        package: jsConfig.package,
        signType: jsConfig.signType,
        paySign: jsConfig.paySign,
        success: function (res) {
          that.setData({ 'userinfo.now_money': app.help().Add(value,that.data.userinfo.now_money)});
          return app.Tips({ title: '支付成功', icon: 'success' },'/pages/user_money/index');
        },
        fail:function(){
          return app.Tips({ title:'支付失败'});
        },
        complete: function (res) {
          if (res.errMsg == 'requestPayment:cancel') return app.Tips({ title:'取消支付'});
        }
      })
    });
  }
})