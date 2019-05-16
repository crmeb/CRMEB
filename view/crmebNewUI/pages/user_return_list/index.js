// pages/return-list/index.js
const app=getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '退货列表',
      'color': false
    },
    loading: false,//是否加载中
    loadend: false,//是否加载完毕
    loadTitle: '加载更多',//提示语
    orderList: [],//订单数组
    orderStatus: -3,//订单状态
    page: 1,
    limit: 10
  },
  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.getOrderList();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (options.isT) this.setData({'parameter.return':0});
  },

  /**
   * 去订单详情
  */
  goOrderDetails: function (e) {
    var order_id = e.currentTarget.dataset.order_id;
    if (!order_id) return app.Tips({ title: '缺少订单号无法查看订单详情' });
    wx.navigateTo({ url: '/pages/order_details/index?order_id=' + order_id +'&isReturen=1' })
  },

  /**
  * 获取订单列表
 */
  getOrderList: function () {
    var that = this;
    if (that.data.loadend) return;
    if (that.data.loading) return;
    that.setData({ loading: true, loadTitle: "" });
    app.baseGet(app.U({
      c: 'user_api', a: 'get_user_order_list', q: {
        type: that.data.orderStatus,
        page: that.data.page,
        limit: that.data.limit,
      }
    }), function (res) {
      var list = res.data || [];
      var loadend = list.length < that.data.limit;
      that.data.orderList = app.SplitArray(list, that.data.orderList);
      that.setData({
        orderList: that.data.orderList,
        loadend: loadend,
        loading: false,
        loadTitle: loadend ? "我也是有底线的" : '加载更多',
        page: that.data.page + 1,
      });
    }, function (res) {
      that.setData({ loading: false, loadTitle: "加载更多" });
    });
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
    this.getOrderList();
  },
})