
var app = getApp();
Page({
  data: {
    url: app.globalData.urlImages,
    fereeArray:[],
    page:1,
    count: '',
    first: 0,
    limit: 20,
    title:"",
    loadinghidden:false
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
       var that = this;
       var header = {
         'content-type': 'application/x-www-form-urlencoded',
       };
       wx.request({
         url: app.globalData.url + '/routine/auth_api/get_spread_list?uid=' + app.globalData.uid,
         method: 'GET',
         data: {
           first: that.data.first,
           limit: that.data.limit
         },
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
  toTwo:function(e){
    if (e.currentTarget.dataset.id) {
      wx.navigateTo({
        url: '/pages/feree-two/feree-two?uid=' + e.currentTarget.dataset.id,
      })
    }
  },
  onReachBottom: function () {
    var that = this;
    var limit = 20;
    var first = that.data.first;
    var startpage = limit * first;
    var array = that.data.fereeArray;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_spread_list?uid=' + app.globalData.uid,
      data: {
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