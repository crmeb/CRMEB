var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '物流信息'
    },
    orderId:'',
    product: { productInfo:{}},
    orderInfo:{},
    expressList:[],
  },

  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.getExpress();
    this.get_host_product();
  },
  copyOrderId:function(){
    wx.setClipboardData({ data: this.data.orderInfo.delivery_id });
  },
  getExpress:function(){
    var that=this;
    app.baseGet(app.U({ c: 'user_api', a: 'express', q: { uni:that.data.orderId}}),function(res){
      var result = res.data.express.result || {};
      that.setData({ 
        product: res.data.order.cartInfo[0] || {}, 
        orderInfo: res.data.order,
        expressList: result.list || [] 
      });
    });
  },
  /**
* 获取我的推荐
*/
  get_host_product: function () {
    var that = this;
    app.baseGet(app.U({ c: 'public_api', a: "get_hot_product", q: { offset: 1, limit: 4 } }), function (res) {
      that.setData({ host_product: res.data });
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (!options.orderId) return app.Tips({title:'缺少订单号'});
    this.setData({ orderId: options.orderId });
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