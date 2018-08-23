// pages/pink-list/index.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    banner:[],
    offset:0,
    limit:20,
    CombinationList:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    this.getCombinationList();
    this.getBanner();
  },
  getBanner:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_combination_list_banner?uid=' + app.globalData.uid,
      method: 'GET',
      dataType: 'json',
      success: function (res) {
        that.setData({
          banner: res.data.data
        })
        console.log(that.data.banner);
      }
    })
  },
  getCombinationList:function(){
     var that = this;
     wx.request({
       url: app.globalData.url + '/routine/auth_api/get_combination_list?uid=' + app.globalData.uid,
       data:{
         offset: that.data.offset,
         limit:that.data.limit
       },
       method: 'GET',
       dataType: 'json',
       success: function(res) {
         that.setData({
           CombinationList:res.data.data
         })
       }
     })
  },
  goDetail:function(e){
     wx.request({
       url: app.globalData.url + '/routine/auth_api/get_form_id?uid=' + app.globalData.uid,
       method: 'GET',
       data: {
         formId: e.detail.formId
       },
       success: function (res) { }
     })
     wx.navigateTo({
       url: '/pages/product-pinke/index?id=' + e.detail.value.id,
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