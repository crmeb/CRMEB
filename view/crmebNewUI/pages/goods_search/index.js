// pages/searchGood/index.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '搜索商品',
      'color': false
    },
    host_product:[],
    searchValue:'',
    focus:true,
    bastList:[],
    hotSearchList:[],
    first: 0,
    limit: 8,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  getRoutineHotSearch: function () {
    var that = this;
    app.baseGet(app.U({ c: 'store_api', a: "get_routine_hot_search", }), function (res) {
      that.setData({ hotSearchList: res.data });
    });
  },
  getProductList:function(){
    var that = this;
    app.baseGet(app.U({ c: 'store_api', a: "goods_search", q: { keyword: that.data.searchValue } }), function (res) {
      that.setData({ bastList: res.data });
    });
  },
  getHostProduct: function () {
    var that = this;
    app.baseGet(app.U({ c: 'public_api', a: "get_hot_product", q: { offset: 1, limit: 4 } }), function (res) {
      that.setData({ host_product: res.data });
    });
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  }, 
  setHotSearchValue: function (event) {
    this.setData({ searchValue: event.currentTarget.dataset.item });
    this.getProductList();
  },
  setValue: function (event){
    this.setData({ searchValue: event.detail.value});
  },
  searchBut:function(){
    var that = this;
    if (that.data.searchValue.length > 0){
      that.getProductList();
    }else{
      wx.showToast({
        title: '请输入要搜索的商品',
        icon: 'none',
        duration: 1000,
        mask: true,
      })
    }
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getRoutineHotSearch();
    this.getHostProduct();
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