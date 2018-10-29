
var app = getApp();
Page({
  data: {
    url: app.globalData.urlImages,
    fereeArray: [],
    page: 1,
    count: '',
    first:0,
    limit:20,
    title: "",
    loadinghidden: false
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    if (options.uid){
      this.setData({
        uid: options.uid
      })
      this.getSpreadListTwo();
    }else{
      wx.showToast({
        title: '参数错误',
        icon: 'none',
        duration: 1000,
      })
      setTimeout(function(){
         wx.navigateTo({
           url: '/pages/feree/feree'
         })
      },1200);
    }
  },
  getSpreadListTwo:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_spread_list_two?uid=' + app.globalData.uid,
      data: {
        two_uid: that.data.uid,
        first: that.data.first,
        limit: that.data.limit
      },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            count: res.data.data.count ? res.data.data.count : 0,
            fereeArray: res.data.data.list,
            loadinghidden: true,
            title: '加载完成'
          })
        }
      }
    });
  },
  onReachBottom: function () {
    var that = this;
    var limit = 20;
    var first = that.data.first;
    var startpage = limit * first;
    var array = that.data.fereeArray;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_spread_list_two?uid=' + app.globalData.uid,
      data: {
        two_uid: that.data.uid,
        limit: limit, 
        first: startpage 
      },
      method: 'GET',
      success: function (res) {
        var len = res.data.data.list.length;
        for (var i = 0; i < len; i++) {
          array.push(res.data.data.list[i])
        }
        that.setData({
          fereeArray: array,
          loadinghidden: true,
          title: '加载完成'
        })      
      }
    })
  }
})