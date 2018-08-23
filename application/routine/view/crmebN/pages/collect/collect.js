var app = getApp();
// pages/collect/collect.js
Page({
  data: {
    url: app.globalData.urlImages,
    sum:'',
    Arraylist:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();   
    this.getcollect();
  },
  getcollect:function(){
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    }
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_collect_product?uid=' + app.globalData.uid,
      method: 'GET',
      header: header,
      success: function (res) {
        if (res.data.code==200){
          that.setData({
            sum: res.data.data.length,
            Arraylist: res.data.data
          })
        }else{
        // console.log(res);
        that.setData({
          sum: 0,
          Arraylist: []
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
  del:function(e){
    var that=this;
    var id = e.target.dataset.id;
    console.log(id);
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_collect_product_del?uid=' + app.globalData.uid,
      data:{pid:id},
      method: 'GET',
      header: header,
      success: function (res) {
        if (res.data.code == 200){
          that.getcollect();
        }  
      }
    })
  },

 
})