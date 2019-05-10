var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    navlist: [],
    productList: [],
    navActive: 0,
    parameter: {
      'navbar': '1',
      'return': '0',
      'title':'产品分类'
    },
    navH:"",
    number:""
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (res) {
    this.getPidCate();
    this.getAllCategory();
  },
  infoScroll:function(){
    var that = this;
    var len = that.data.productList.length;
    that.setData({
      navH: app.globalData.navHeight,
      number: that.data.productList[len - 1].child.length
    })
    //设置商品列表高度
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          height: (res.windowHeight) * ( 750/res.windowWidth) - 98 - app.globalData.navHeight
          //res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
        })
      },
    });
    var height = 0;
    var hightArr = [];
    for (var i = 0; i < len; i++) { //productList
      //获取元素所在位置
      var query = wx.createSelectorQuery().in(this);
      var idView = "#b" + i;
      query.select(idView).boundingClientRect();
      query.exec(function (res) {
        var top = res[0].top;
        hightArr.push(top);
        that.setData({
          hightArr: hightArr
        });
      });
    };
  },
  tap: function (e) {
    var id = e.currentTarget.dataset.id;
    var index = e.currentTarget.dataset.index;
    console.log(index, id);
    this.setData({
      toView: id,
      navActive: index
    });
  },
  getPidCate:function(){
    var that=this;
    app.baseGet(app.U({ c: 'store_api', a:"get_pid_cate"}),function(res){
      that.setData({
        navlist:res.data
      });
    });
  },
  getAllCategory:function(){
    var that = this;
    app.baseGet(app.U({ c: 'store_api', a: "get_product_category" }), function (res) {
      that.setData({
        productList: res.data
      });
      that.infoScroll();
    });
  },
  scroll: function (e) {
    var scrollTop = e.detail.scrollTop;
    var scrollArr = this.data.hightArr;
    for (var i = 0; i < scrollArr.length; i++) {
      if (scrollTop >= 0 && scrollTop < scrollArr[1] - scrollArr[0]) {
        this.setData({
          navActive: 0,
          lastActive: 0
        })
      } else if (scrollTop >= scrollArr[i] - scrollArr[0] && scrollTop < scrollArr[i + 1] - scrollArr[0]) {
        console.log(scrollArr[1] - scrollArr[0])
        this.setData({
          navActive: i
        })
      } else if (scrollTop >= scrollArr[scrollArr.length - 1] - scrollArr[0]) {
        this.setData({
          navActive: scrollArr.length - 1
        })
      }
    }
  },

  searchSubmitValue: function (e) {
    var that = this;
    app.baseGet(app.U({ c:"public_api",a: "get_form_id", q: { formId: e.detail.formId } }),null,null,true);
    if (e.detail.value.length > 0) wx.navigateTo({ url: '/pages/goods_list/goods_list?searchValue=' + e.detail.value})
    else return app.Tips({ title:'请填写要搜索的产品信息'});
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

  }
})