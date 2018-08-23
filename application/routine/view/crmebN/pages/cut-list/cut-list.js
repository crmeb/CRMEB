var app = getApp();
// pages/cut-list/cut-list.js
Page({

  data: {
    url: app.globalData.urlImages,
    cutList:[],
    titleImage:'',
    title: [],
    lovely:'',
    indicatorDots: true,//是否显示面板指示点;
    autoplay: true,//是否自动播放;
    interval: 3000,//动画间隔的时间;
    duration: 500,//动画播放的时长;
    vertical: true,
    circular: true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.cut_list();
    
  },
cut_list:function(){
  var that = this;
  wx.request({
    url: app.globalData.url + '/routine/auth_api/get_bargain_list?uid=' + app.globalData.uid,
    method: 'POST',
    success: function (res) {
      if (res.data.code == 200) {
      that.setData({
        cutList: res.data.data.bargain,
        titleImage: res.data.data.banner,
        lovely: res.data.data.lovely,
        title: res.data.data.bargainUser
      })
      }else{
        that.setData({
          cutList: []
        })
      }
    }
  })
},
  cut:function(e){
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../../pages/cut-one/cut-one?id='+id,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  }
  
})