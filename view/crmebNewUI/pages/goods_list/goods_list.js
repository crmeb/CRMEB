var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    productList:[],
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '商品列表',
      'color': true,
      'class': '0'
    },
    navH: "",
    is_switch:true,
    where: {
      sid: 0,
      keyword: '',
      priceOrder: '',
      salesOrder: '',
      news: 0,
      page: 1,
      limit: 10,
      cid: 0,
    },
    price:0,
    stock:0,
    nows:false,
    loadend:false,
    loading:false,
    loadTitle:'加载更多',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({ 
      ['where.sid']: options.sid || 0, 
      title: options.title || '', 
      ['where.keyword']: options.searchValue || '', 
      navH: app.globalData.navHeight
    });
    this.get_product_list();
    this.get_host_product();
  },
  Changswitch:function(){
     var that = this;
     that.setData({
       is_switch: !this.data.is_switch
     })
  },
  searchSubmit: function (e) {
    var that = this;
    this.setData({ ['where.keyword']: e.detail.value, loadend: false, ['where.page']: 1 })
    this.get_product_list(true);
  },
  /**
  * 获取我的推荐
  */
  get_host_product: function () {
    var that = this;
    app.baseGet(app.U({ c: 'public_api', a: "get_hot_product", q: { offset: 1, limit: 4 } }), function (res) {
      that.setData({ host_product: res.data });
    });
  },
  //点击事件处理
  set_where: function (e) {
    var dataset = e.target.dataset;
    switch (dataset.type) {
      case '1':
        return wx.navigateBack({
          delta: 1,
        })
        break;
      case '2':
        if (this.data.price == 0)
          this.data.price = 1;
        else if (this.data.price == 1)
          this.data.price = 2;
        else if (this.data.price == 2)
          this.data.price = 0;
        this.setData({ price: this.data.price, stock: 0 });
        break;
      case '3':
        if (this.data.stock == 0)
          this.data.stock = 1;
        else if (this.data.stock == 1)
          this.data.stock = 2;
        else if (this.data.stock == 2)
          this.data.stock = 0;
        this.setData({ stock: this.data.stock, price: 0 });
        break;
      case '4':
        this.setData({ nows: !this.data.nows });
        break;
    }
    this.setData({ loadend: false, ['where.page']: 1 });
    this.get_product_list(true);
  },
  //设置where条件
  setWhere: function () {
    if (this.data.price == 0)
      this.data.where.priceOrder = '';
    else if (this.data.price == 1)
      this.data.where.priceOrder = 'desc';
    else if (this.data.price == 2)
      this.data.where.priceOrder = 'asc';
    if (this.data.stock == 0)
      this.data.where.salesOrder = '';
    else if (this.data.stock == 1)
      this.data.where.salesOrder = 'desc';
    else if (this.data.stock == 2)
      this.data.where.salesOrder = 'asc';
    this.data.where.news = this.data.nows ? 1 : 0;
    this.setData({ where: this.data.where });
  },
  //查找产品
  get_product_list: function (isPage) {
    var that = this;
    this.setWhere();
    if (that.data.loadend) return;
    if (that.data.loading) return;
    if (isPage === true) that.setData({ productList: [] });
    that.setData({ loading: true, loadTitle: '' });
    app.baseGet(app.U({ c:'store_api',a: 'get_product_list', q: that.data.where }), function (res) {
      var list = res.data;
      var productList = app.SplitArray(list, that.data.productList);
      var loadend = list.length < that.data.where.limit;
      that.setData({
        loadend: loadend,
        loading: false,
        loadTitle: loadend ? '已全部加载' : '加载更多',
        productList: productList,
        ['where.page']: that.data.where.page + 1,
      });
    }, function (res) {
      that.setData({ loading: false, loadTitle: '加载更多' });
    }, true);
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
    this.setData({['where.page']:1,loadend:false,productList:[]});
    this.get_product_list();
    wx.stopPullDownRefresh();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.get_product_list();
  },
})