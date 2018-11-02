
var app = getApp();
// pages/comment/comment.js
Page({
  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    comm:1,
    productId:'',
    uinfo:[],
    alllength:'',
    newlength:'',
    piclength:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor(); 
    app.setUserInfo();
    var productId = options.productId;
    this.setData({
      productId: productId
    })
    this.comment();
    this.alllen();
    this.newlen();
    this.piclen();
  },
  alllen:function(){
    var that = this;
    var productId = that.data.productId;
    var filter='all';
    wx.request({
      url: app.globalData.url + '/routine/auth_api/product_reply_list?uid=' + app.globalData.uid,
      data: { productId: productId, filter: filter },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            alllength: res.data.data.length
          })
        } else {
          that.setData({
            alllength: ''
          })
        }
      }
    })
  },
  newlen: function () {
    var that = this;
    var productId = that.data.productId;
    var filter = 'new';
    wx.request({
      url: app.globalData.url + '/routine/auth_api/product_reply_list?uid=' + app.globalData.uid,
      data: { productId: productId, filter: filter },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            newlength: res.data.data.length
          })
        } else {
          that.setData({
            newlength: ''
          })
        }
      }
    })
  },
  piclen: function () {
    var that = this;
    var productId = that.data.productId;
    var filter = 'pic';
    wx.request({
      url: app.globalData.url + '/routine/auth_api/product_reply_list?uid=' + app.globalData.uid,
      data: { productId: productId, filter: filter },
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            piclength: res.data.data.length
          })
        } else {
          that.setData({
            piclength: ''
          })
        }
      }
    })
  },
  comment: function (){
    var that=this;
    var comm = that.data.comm;
    var filter='';
    if (comm==1){
      filter = 'all';
    } else if (comm == 2){
      filter = 'new';
    } else if (comm == 3) {
      filter = 'pic';
    } else{
      return false;
    }
    var productId = that.data.productId;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/product_reply_list?uid=' + app.globalData.uid,
      data: { productId: productId, filter: filter},
      method: 'GET',
      success: function (res) {
       if(res.data.code==200){
         that.setData({
           uinfo: res.data.data,
           replyCount: res.data.data.length
         })
       }else{
         that.setData({
           uinfo: [],
           replyCount:''
         })
       }
      }
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },
  commentap:function(e){
    var index = e.target.dataset.index;
    this.setData({
      comm: index
    })
    this.comment();
  },
  getImagePreview:function(e){//图片预览
    wx.previewImage({
      current: e.currentTarget.dataset.image, // 当前显示图片的http链接
      urls: e.currentTarget.dataset.images // 需要预览的图片http链接列表
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