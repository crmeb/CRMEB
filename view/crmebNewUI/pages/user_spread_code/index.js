// pages/distribution-posters/index.js
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '分销海报'
    },
    imgUrls: [],
    indicatorDots: false,
    circular: false,
    autoplay: false,
    interval: 3000,
    duration: 500,
    swiperIndex: 0,
    spreadList:[],
    userInfo:{},
    poster:'',
  },
  onLoadFun:function(){
    this.getUserInfo();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  bindchange(e) {
    var spreadList = this.data.spreadList;
    this.setData({
      swiperIndex: e.detail.current,
      poster: spreadList[e.detail.current].poster,
    })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  getUserInfo:function(){
    var that=this;
    app.baseGet(app.U({ c: 'user_api', a:'get_my_user_info'}),function(res){
      that.setData({userInfo:res.data});
    });
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.userSpreadBannerList();
  },
  savePosterPath: function () {
    var that = this;
    wx.downloadFile({
      url: that.data.poster,
      success(resFile) {
        console.log(resFile.tempFilePath)
        if (resFile.statusCode === 200) {
          wx.getSetting({
            success(res) {
              if (!res.authSetting['scope.writePhotosAlbum']) {
                wx.authorize({
                  scope: 'scope.writePhotosAlbum',
                  success() {
                    wx.saveImageToPhotosAlbum({
                      filePath: resFile.tempFilePath,
                      success: function (res) {
                        wx.showToast({
                          title: '保存成功',
                          icon: 'success',
                          duration: 1500,
                        })
                      },
                      fail: function (res) {
                        wx.showToast({
                          title: res.errMsg,
                          icon: 'none',
                          duration: 1500,
                        })
                      },
                      complete: function (res) { },
                    })
                  }
                })
              } else {
                wx.saveImageToPhotosAlbum({
                  filePath: resFile.tempFilePath,
                  success: function (res) {
                    wx.showToast({
                      title: '保存成功',
                      icon: 'success',
                      duration: 1500,
                    })
                  },
                  fail: function (res) {
                    wx.showToast({
                      title: res.errMsg,
                      icon: 'none',
                      duration: 1500,
                    })
                  },
                  complete: function (res) { },
                })
              }
            }
          })
        }else{
          wx.showToast({
            title: resFile.errMsg,
            icon: 'none',
            duration: 1000,
            mask: true,
          })
        }
      },
      fail(res) {
        wx.showToast({
          title: res.errMsg,
          icon: 'none',
          duration: 1000,
          mask: true,
        })
      }
    })
  },
  userSpreadBannerList: function () {
    var that = this;
    wx.showLoading({
      title: '获取中',
      mask: true,
    })
    app.baseGet(app.U({ c: 'user_api', a: 'user_spread_banner_list' }), function (res) {
      wx.hideLoading();
      that.setData({ spreadList: res.data ,poster: res.data[0].poster});
    }, function (res) { wx.hideLoading(); });
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
  set_user_share: function () {
    app.baseGet(app.U({ c: 'public_api', a: 'set_user_share' }), null, null, true);
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    this.set_user_share();
    return {
      title: this.data.userInfo.nickname+'-分销海报',
      imageUrl: this.data.spreadList[0],
      path: '/pages/index/index?spid=' + this.data.userInfo.uid,
    };
  }
})