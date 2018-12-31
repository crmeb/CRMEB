var app = getApp();
// pages/unshop/unshop.js
Page({
  data: {
    url: app.globalData.urlImages,
    Arraylike: [],
    order:[],
    express:[],
    productid:''
    
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var orderid = options.orderId;
    this.express(orderid);
    this.likeproduct();
    // console.log(orderid);
  },
  express: function (orderid){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/express?uid='+app.globalData.uid +'&uni='+orderid,
      method: 'GET',
      success: function (res) {
        if(res.data.code==200){
        that.setData({
          order: res.data.data.order,
          express: res.data.data.express
        })
        }else{
          that.setData({
            order: [],
            express: []
          })
        }
      }
    })
  },
  likeproduct:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_hot_product',
      data: { offset: 0, limit:4},
      method: 'POST',
      success: function (res) {
        console.log(res.data.data);
        if (res.data.code == 200) {
          that.setData({
            Arraylike: res.data.data
          })
        } else {
          that.setData({
            Arraylike: []
          })
        }
      }
    })
  },
  cart: function (e) {
    var that = this;
    var id = e.target.dataset.id;
    console.log(id);
    wx.request({
      url: app.globalData.url + '/routine/auth_api/unique?uid=' + app.globalData.uid + '&productId=' + id,
      // data: { productId:id},
      method: 'GET',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            productid: id
          })
          that.prompt();
        } else {
          that.setData({
            productid: ''
          })
          that.prompts();
        }
      }

    })
  },
  prompt: function () {
    wx.showToast({
      title: this.data.productid ? "加入成功" : "加入失败",
      icon: 'success',
      duration: 800,
      mask: true
    })
  },
  prompts: function () {
    wx.showToast({
      title: '加入购物车失败！',
      icon: 'none',
      duration: 2000//持续的时间

    })
  },
})