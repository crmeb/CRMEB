// pages/flash-sale/index.js
var app = getApp();
const wxh = require('../../../utils/wxh.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    topImage:'',
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '限时秒杀',
      'color': false
    },
    seckillList:[],
    timeList:[],
    active:5,
    scrollLeft:0,
    interval:0,
    status:1,
    countDownHour: "00",
    countDownMinute: "00",
    countDownSecond: "00",
    offset : 0,
    limit : 20,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (e) {
  },
  onLoadFun: function () {
    this.getSeckillConfig();
  },
  goDetails:function(e){
    wx.navigateTo({
      url: '/pages/activity/goods_seckill_details/index?id=' + e.currentTarget.dataset.id + '&time=' + this.data.timeList[this.data.active].stop,
    })
  },
  settimeList:function(e){
    var that = this;
    that.setData({ active: e.currentTarget.dataset.index });
    if (that.data.interval) {
      clearInterval(that.data.interval);
      that.setData({ interval: null });
    }
    that.setData({ 
      interval: 0, 
      countDownHour: "00",
      countDownMinute: "00",
      countDownSecond: "00",
      status: that.data.timeList[that.data.active].status
    });
    wxh.time(e.currentTarget.dataset.stop, that);
    that.setData({ seckillList: [], offset: 0 });
    that.getSeckillList();
  },
  getSeckillConfig: function () {
    var that = this; 
    app.baseGet(app.U({ c: "seckill_api", a:'seckill_index'}), function (res){
      that.setData({ topImage: res.data.lovely, timeList: res.data.seckillTime, active: res.data.seckillTimeIndex });
      if (that.data.timeList.length) {
        wxh.time(that.data.timeList[that.data.active].stop, that);
        that.setData({ scrollLeft: (that.data.active - 1.37) * 100 });
        setTimeout(function () { that.setData({ loading: true }) }, 2000);
        that.setData({ seckillList: [], offset: 0 });
        that.setData({ status: that.data.timeList[that.data.active].status });
        that.getSeckillList();
      }
    },function(res){ console.log(res)});
  },
  getSeckillList: function () {
    var that = this; 
    var data = { offset: that.data.offset, limit: that.data.limit,time: that.data.timeList[that.data.active].id};
    app.basePost(app.U({ c: 'seckill_api', a:"seckill_list"}), data, function (res) {
      var seckillList = that.data.seckillList;
      var limit = that.data.limit;
      var offset = that.data.offset;
      that.setData({ 
        seckillList: seckillList.concat(res.data), 
        offset: Number(offset) + Number(limit)
      });
    }, function (res) { console.log(res) });
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
    if(this.data.interval){
      clearInterval(this.data.interval);
      this.setData({ interval: null });
    }
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
    this.getSeckillList();
  }
})