var app = getApp();
// pages/promotion-order/promotion-order.js
Page({
  data: {
    url: app.globalData.urlImages,
    currentTab:"",
    hiddens:true,
    icondui:0,
    icondui2: 0,
    alloeder: ['全部订单', '已评价', '已发货'],
    allOrder:"全部订单",
    promoter:"推广粉丝",
    promoterList: [],
    orderlist:[],
    orderconut:'',
    ordermoney:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    this.orderlist();
    this.extension(header);
    this.orderlistmoney();
    
  },
  extension: function (header){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_spread_list?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        // console.log(res.data.data);
        if (res.data.code==200){
          that.setData({
            promoterList: res.data.data.list
          })
        }else{
        that.setData({
          promoterList: []
        })
        }
      }
    });
  },
  spread: function () {
    wx.navigateTo({
      url: '../../pages/spread/spread',
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
 
  ordertap:function(e){
    var currentTab = e.target.dataset.index;
    this.setData({
     currentTab:currentTab,
     hiddens:false
    })
    console.log(this.data.currentTab)
  },
  icondui:function(e){
    var that=this;
    var icondui = e.target.dataset.ider;

    that.setData({
      icondui: icondui,
      hiddens: true,
      allOrder: that.data.alloeder[icondui],
      currentTab: -1
    })
    that.orderlist();
    this.orderlistmoney();
  },
  icondui2:function(e){
    var that = this;
    var icondui2 = e.target.dataset.ider;
    var promoterLists = that.data.promoterList;
    var len = promoterLists.length;
    for (var index in promoterLists){
      if (promoterLists[index]['uid'] == icondui2){
        var promoter = promoterLists[index]['nickname'];
      }
    }

    that.setData({
      icondui2: icondui2,
      hiddens: true,
      promoter: promoter,
      currentTab: -1
    })
    that.orderlist();
  },
  zhaoguan:function(e){
    this.setData({
      hiddens: true,
      currentTab: -1
    })
  },
  orderlist: function (){
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
   var that = this;
   var icondui = that.data.icondui;
   var icondui2 = that.data.icondui2;
   wx.request({
     url: app.globalData.url + '/routine/auth_api/subordinateOrderlist?uid=' + app.globalData.uid,
     data: { uid: icondui2, status: icondui},
     method: 'POST',
     header: header,
     success: function (res) { 
       if (res.data.code==200){
         that.setData({
           orderlist: res.data.data
         })
       }else{
       that.setData({
         orderlist: []
       })
       }
     }
   })
 },
  orderlistmoney: function () {
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/subordinateOrderlistmoney?uid=' + app.globalData.uid,
      data: { status: that.data.icondui},
      method: 'POST',
      success: function (res) {
        if (res.data.code == 200) {
          that.setData({
            ordermoney: res.data.data.sum,
            orderconut: res.data.data.cont
          })
        } else {
          that.setData({
            ordermoney:'',
            orderconut:''
          })
        }
      }
    })
  }
  
})