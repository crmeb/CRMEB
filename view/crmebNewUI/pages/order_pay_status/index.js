var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '支付成功'
    },
    orderId:'',
    order_pay_info: { paid :1 }
  },
  onLoadFun:function(){
    this.getOrderPayInfo();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (!options.order_id) return app.Tips({title:'缺少参数无法查看订单支付状态'},{tab:3,url:1});
    this.setData({ orderId: options.order_id, status: options.status || 0, msg: options.msg || ''});
  },
  /**
   * 
   * 支付完成查询支付状态
   * 
  */
  getOrderPayInfo:function(){
    var that=this;
    wx.showLoading({title: '正在加载中'});
    app.baseGet(app.U({ c: 'auth_api', a: 'get_order_pay_info', q: { order_id: this.data.orderId}}),function(res){
      wx.hideLoading();
      that.setData({ order_pay_info: res.data, 'parameter.title': res.data.paid ? '支付成功' :'支付失败'});
    },function(){
      wx.hideLoading();
    });
  },
  /**
   * 去首页关闭当前所有页面
  */
  goIndex:function(e){
    var formId = e.detail.formId;
    app.baseGet(app.U({ c: 'public_api', a: 'get_form_id', q: { formId: formId } }), null, null, true);
    wx.switchTab({url:'/pages/index/index'});
  },

  /**
   * 
   * 去订单详情页面
  */
  goOrderDetails:function(e)
  {
    var formId = e.detail.formId;
    app.baseGet(app.U({ c: 'public_api', a: 'get_form_id', q: { formId: formId } }), null, null, true);
    wx.navigateTo({
      url: '/pages/order_details/index?order_id=' + this.data.orderId
    });
  }


})