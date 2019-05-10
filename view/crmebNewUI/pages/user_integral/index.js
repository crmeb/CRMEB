// pages/integral-details/index.js
const app=getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '积分详情',
      'color': true,
      'class': '0'
    },
    navList:[
      { 'name': '分值明细', 'icon':'icon-mingxi'},
      { 'name': '分值提升', 'icon': 'icon-tishengfenzhi' }
    ],
    current:0,
    page:1,
    limit:10,
    integralList:[],
    loadend:false,
    loading:false,
    loadTitle:'加载更多',
  },
  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.getUserInfo();
    this.getIntegralList();
  },
  getUserInfo:function(){
    var that=this;
    app.baseGet(app.U({ c: 'user_api', a: 'get_my_user_info', q: { isIntegral:1}}),function(res){
      that.setData({userInfo:res.data});
    });
  },

  /**
   * 获取积分明细
  */
  getIntegralList:function(){
    var that=this;
    if(that.data.loading) return;
    if(that.data.loadend) return;
    that.setData({loading:true,loadTitle:''});
    app.baseGet(app.U({ c: 'user_api', a:'user_integral_list',q:{page:that.data.page,limit:that.data.limit}}),function(res){
      var list=res.data,loadend=list.length < that.data.limit;
      that.data.integralList = app.SplitArray(list,that.data.integralList);
      that.setData({
        integralList: that.data.integralList,
        page:that.data.page+1,
        loading:false,
        loadend:loadend,
        loadTitle:loadend ? '哼~😕我也是有底线的~':"加载更多"
      });
    },function(res){
      that.setData({ loading: false, loadTitle:'加载更多'});
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  
  nav:function(e){
     this.setData({
       current: e.currentTarget.dataset.idx
     })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.getIntegralList();
  }
})