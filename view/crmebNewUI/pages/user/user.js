const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '0',
      'title': '个人中心',
      'color': true,
      'class': '0'
    },
    userInfo:{},
    MyMenus:[],
  },

  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.getUserInfo();
    if (!this.data.MyMenus.length) this.getMyMenus();
  },

  /**
   * 
   * 获取个人中心图标
  */
  getMyMenus: function () {
    var that = this;
    app.baseGet(app.U({ c: 'public_api', a: 'get_my_naviga' }), function (res) {
      that.setData({MyMenus:res.data.routine_my_menus});
    });
  },
  /**
   * 小程序设置
  */
  Setting:function(){
    wx.openSetting({
      success:function(res){
        console.log(res.authSetting)
      }
    });
  },
  /**
   * 跳转到用户资料
  */
  goUserInfo:function(){
    wx.navigateTo({
      url: '/pages/user_info/index',
    })
  },
  /**
   * 获取个人用户信息
  */
  getUserInfo:function(){
    var that=this;
    app.baseGet(app.U({c:'user_api',a:'my'}),function(res){
      that.setData({userInfo:res.data});
    });
  },
  /**
   * 页面跳转
  */
  goPages:function(e){
    if (e.currentTarget.dataset.url == '/pages/user_spread_user/index' && this.data.userInfo.statu==1) {
      if (!this.data.userInfo.is_promoter) return app.Tips({ title: '您还没有推广权限！！' });
    }
    wx.navigateTo({
      url: e.currentTarget.dataset.url
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({ MyMenus:app.globalData.MyMenus});
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
  onShow:function(){
    if (app.globalData.isLog) this.getUserInfo();
  },

  /**
  * 生命周期函数--监听页面卸载
  */
  onUnload: function () {

  },
})