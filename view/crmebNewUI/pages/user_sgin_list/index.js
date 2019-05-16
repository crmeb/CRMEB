// pages/sign-record/index.js
const app=getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '签到记录',
      'color': false
    },
    loading:false,
    loadend:false,
    loadtitle:'加载更多',
    page:1,
    limit:8,
    signList:[],
  },

  /**
   * 
   * 授权回调
  */
  onLoadFun:function(){
    this.getSignMoneList();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 获取签到记录列表
  */
  getSignMoneList:function(){
    var that=this;
    if(that.data.loading) return;
    if(that.data.loadend) return;
    that.setData({loading:true,loadtitle:""});
    app.baseGet(app.U({ c: 'user_api', a:'get_sign_month_list',q:{page:that.data.page,limit:that.data.limit}}),function(res){
      var list = res.data.data;
      var loadend=list.length < that.data.limit;
      that.data.signList = app.SplitArray(list,that.data.signList);
      that.setData({
        signList:that.data.signList,
        loadend:loadend,
        loading:false,
        loadtitle:loadend ? "哼😕~我也是底线的~":"加载更多"
      });
    },function(){
      that.setData({ loading: false, loadtitle:'加载更多'});
    });
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.getSignMoneList();
  },
})