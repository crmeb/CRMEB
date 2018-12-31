var app = getApp();
var wxh = require('../../utils/wxh.js');
Page({
  data: {
    Arraylist:[],
    insertTime:[],
    timeer:'',
    lovely:'',
    url: app.globalData.urlImages,
  },
  setTouchMove: function (e) {
    var that = this;
    wxh.home(that, e);
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.getList();
  },
  getList: function () {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/seckill_index?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        that.setData({
          lovely: res.data.data.lovely
        })
        if (res.data.data.seckill.length > 0){
          that.setData({
            Arraylist: res.data.data.seckill
          })
          that.setTime();
        }
      }
    })
  },
  timeFormat(param){//小于10的格式化函数
    return param < 10 ? '0' + param : param; 
  },
  setTime: function () {//到期时间戳
    var that = this;
    var newTime = new Date().getTime() / 1000;
    var endTimeList = that.data.Arraylist;
    var countDownArr = [];
    for (var i in endTimeList){
      var endTime = endTimeList[i].stop_time;
      var obj = [];
      if (endTime - newTime > 0){
        var time = endTime - newTime;
        var day = parseInt(time / (60 * 60 * 24));
        var hou = parseInt(time % (60 * 60 * 24) / 3600);
        var min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
        var sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
        hou = parseInt(hou) + parseInt(day * 24);
        obj = {
          day: that.timeFormat(day),
          hou: that.timeFormat(hou),
          min: that.timeFormat(min),
          sec: that.timeFormat(sec)
        }
      }else{
        obj = {
          day: '00',
          hou: '00',
          min: '00',
          sec: '00'
        }
        that.getList();
      }
      endTimeList[i].time = obj;
    }
    that.setData({
      Arraylist: endTimeList
    })
    var timeer = setTimeout(that.setTime, 1000);
    that.setData({
      timeer: timeer
    })
  },
  toProduct:function(e){
    var that = this;
    var id = e.currentTarget.dataset.id;
    clearTimeout(that.data.timeer);
    wx.navigateTo({
      url: '/pages/product-countdown/index?id=' + id
    })
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