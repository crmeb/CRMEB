// pages/mycut/mycut.js
var app = getApp();
var wxh = require('../../utils/wxh.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    product:[],
    timeer:'',
    countDownDay:"00",
    countDownHour:"00",
    countDownMinute:"00",
    countDownSecond:"00"
  },

  setTouchMove: function (e) {
    var that = this;
    wxh.home(that, e);
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var that = this;
    var timeStamp = "1912245455"
    wxh.time2(timeStamp, that);
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
    this.getUserBargainAll();
  },
  getUserBargainAll:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_bargain_all?uid=' + app.globalData.uid,
      method: 'GET',
      success: function (res) {
        console.log(res);
        if (res.data.code == 200){
          that.setData({
            product:res.data.data
          })
          that.setTime();
        }else{
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
        }
      }
    })
  },

  timeFormat(param) {//小于10的格式化函数
    return param < 10 ? '0' + param : param;
  },
  setTime: function () {//到期时间戳
    var that = this;
    var newTime = new Date().getTime() / 1000;
    var endTimeList = that.data.product;
    var countDownArr = [];
    for (var i in endTimeList) {
      var endTime = endTimeList[i].stop_time;
      var obj = [];
      if (endTime - newTime > 0) {
        var time = endTime - newTime;
        var day = parseInt(time / (60 * 60 * 24));
        var hou = parseInt(time % (60 * 60 * 24) / 3600);
        var min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
        var sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
        // hou = parseInt(hou) + parseInt(day * 24);
        obj = {
          day: that.timeFormat(day),
          hou: that.timeFormat(hou),
          min: that.timeFormat(min),
          sec: that.timeFormat(sec)
        }
        endTimeList[i].time = obj;
        that.setData({
          product: endTimeList
        })
      } else {
        obj = {
          day: '00',
          hou: '00',
          min: '00',
          sec: '00'
        }
        endTimeList[i].time = obj;
        if (endTimeList[i].status == 1) {
          that.setBargainStatusError(endTimeList, endTimeList[i].id);
        }
      }
    }
    // console.log(that.data.product);
    var timeer = setTimeout(that.setTime, 1000);
    that.setData({
      timeer: timeer
    })
  },
  goCut:function(e){
    var bargainId = e.target.dataset.id;
    wx.navigateTo({
      url: '/pages/cut-one/cut-one?id=' + bargainId,
    })
  },
  goCutList:function(){
    wx.navigateTo({
      url: '/pages/cut-list/cut-list'
    })
  },
  setBargainStatusError: function (endTimeList,bargainUserTableId){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/set_user_bargain_status?uid=' + app.globalData.uid,
      method: 'GET',
      data:{
        bargainUserTableId: bargainUserTableId
      },
      success: function (res) {
        if (res.data.code == 200) {
          endTimeList.forEach(function (value, index, endTimeList){
            if (endTimeList[index].id == bargainUserTableId){
              endTimeList[index].status = 2
            }
          })
          that.setData({
            product: endTimeList
          })
        } else {
          
        }
      }
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})