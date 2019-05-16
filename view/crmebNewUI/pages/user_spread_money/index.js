// pages/commission-details/index.js
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '佣金明细',
      'color': true,
      'class': '0'
    },
    name:'',
    type:0,
    page:0,
    limit:8,
    recordList:[],
    recordType:0,
    recordCount:0,
    status:false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({ type: options.type });
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
    var type = this.data.type;
    if (type == 1) {
      this.setData({ 'parameter.title': '提现记录', name: '提现总额', recordType: 4 });
    } else if (type == 2) {
      this.setData({ 'parameter.title': '佣金记录', name: '佣金明细', recordType: 3 });
    } else {
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000,
        mask: true,
        success: function (res) { setTimeout(function () { wx.navigateBack({ delta: 1, }) }, 1200) },
      });
    }
    this.getRecordList();
    this.getRecordListCount();
  },
  /**
   * 获取余额使用记录
   */
  getRecordList: function () {
    var that = this;
    var page = that.data.page;
    var limit = that.data.limit;
    var status = that.data.status;
    var recordType = that.data.recordType;
    var recordList = that.data.recordList;
    var recordListNew = [];
    if (status == true)return ;
    app.baseGet(app.U({ c: 'user_api', a: 'get_user_bill_list', q: { page: page, limit: limit, type: recordType } }), function (res) {
      var len = res.data.length;
      var recordListData = res.data;
      recordListNew = recordList.concat(recordListData);
      that.setData({ status: limit > len, page: limit + page, recordList: recordListNew });
    });
  },
  getRecordListCount:function(){
    var that = this;
    app.baseGet(app.U({ c: 'user_api', a: 'get_record_list_count', q: { type: that.data.recordType }}), function (res) {
      that.setData({ recordCount: res.data });
    });
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
    this.getRecordList();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})