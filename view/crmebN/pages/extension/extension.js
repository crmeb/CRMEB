var app = getApp();
Page({
  data: {
    url: app.globalData.urlImages,
    userinfo: [],
    yearsum:'',
    extractsum:''
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    this.money(header);
    this.Yesterday_commission(header);
    this.extract(header);
  },
  money: function (header) {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/my?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            userinfo: res.data.data
          })
        }else{
          that.setData({
            userinfo: []
          })
        }
      }
    })
  },
  cash: function () {
    var that = this;
    wx.navigateTo({
      url: '/pages/cash/cash?uid='+ app.globalData.uid,
    })
  },
  Yesterday_commission: function (header) {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/yesterdayCommission?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
        that.setData({
          yearsum: res.data.data
        })
        }else{
          that.setData({
            yearsum: ''
          })
        }
      }
    })
  },
  extract: function (header) {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/extractsum?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
        that.setData({
          extractsum: res.data.data
        })
        }else{
          that.setData({
            extractsum: ''
          })
        }
      }
    })
  },
  spread: function () {
    wx.navigateTo({
      url: "/pages/spread/spread",
    })
  }
})