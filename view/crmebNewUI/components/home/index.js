// components/home/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    homeActive:false
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  setTouchMove: function (e) {
    var that = this;
    if (e.touches[0].clientY < 400 && e.touches[0].clientY > 66) {
      that.setData({
        top: e.touches[0].clientY
      })
    }
  },
  open:function(){
     this.setData({
       homeActive: !this.data.homeActive
     })
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

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})