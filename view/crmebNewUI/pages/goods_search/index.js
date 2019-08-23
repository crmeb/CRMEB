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
    page:1,
    loading:false,
    loadend:false,
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
    if(this.data.loading) return;
    if(this.data.loadend) return;
    this.setData({loading:true,loadTitle:'正在搜索'});
    app.baseGet(app.U({ c: 'store_api', a: "goods_search", q: 
      { 
        keyword: that.data.searchValue,
        page:this.data.page,
        limit:this.data.limit 
      } 
    }), function (res) {
      wx.hideLoading();
      var list = res.data, loadend = list.length < that.data.limit;
      that.data.bastList = app.SplitArray(list, that.data.bastList);
      that.setData({ 
        bastList: that.data.bastList,
        loading:false,
        loadend: loadend,
        page:that.data.page+1,
        loadTitle: loadend ? '已全部加载': '加载更多',
      });
    },function(){
      wx.hideLoading();
      that.setData({ loading: false, loadTitle:"加载更多"});
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
      that.setData({ page: 1, loadend: false, bastList:[]});
      wx.showLoading({ title:'正在搜索中'});
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
    this.getProductList();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})