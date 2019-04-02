// pages/sign/sign.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    userinfo:{},
    sign_day_num: [],
    sign_index:0,
    sign_count:[],
    day:'',
    sign_list:[],
    where:{
      page:1,
      limit:20,
    },
    title:'加载更多',
    loaged:false,
  },
  get_sign_list:function(){
    var that = this;
    if (this.data.loaged==true) return ;
    app.baseGet(app.U({ a: 'get_sign_log', q: this.data.where}), function (res) {
      var sign_list = that.data.sign_list, leng = res.data.list.length;
      for (var i = 0; i < leng;i++){
        sign_list.push(res.data.list[i]);
      }
      that.setData({
        sign_list: sign_list,
        where: { page: res.data.page,limit:20}
      })
      if (res.data.list.length < that.data.where.limit){
        that.setData({
          loaged:true,
          title:'没有更多了',
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setUserInfo();
    app.setBarColor();
    var that = this;
    app.baseGet(app.U({ a: 'get_user_info', q: { is_sign: 1, discount:1}}), function (res) {
      var sign_count = res.data.sign_count;
      that.setData({ 
        userinfo: res.data, 
        sign_index: res.data.sign_num, 
        sign_count: that.prefixInteger(sign_count, 4).split('')
        });
    });
    app.baseGet(app.U({ a: 'get_sign_list' }), function (res) {
      that.setData({ 
        sign_day_num: res.data,
        day: app.rp(res.data.length)
      });
    });
    this.get_sign_list();
  },
  prefixInteger:function(num,length) {
    return (Array(length).join('0') + num).slice(-length);
  },
  sign:function(){
    var that = this;
    app.baseGet(app.U({ a: 'user_sign' }), function (res) {
      var userinfo=that.data.userinfo;
      userinfo.is_sign=true;
      that.setData({
        sign_index: (that.data.sign_index + 1) > that.data.sign_day_num.length ? 1 : that.data.sign_index + 1,
        sign_count: that.prefixInteger(that.data.userinfo.sign_count+1, 4).split(''),
        userinfo: userinfo
      });
      app.Tips({
        title:res.msg,
        icon:'success'
      });
    });
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
    
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.get_sign_list();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})