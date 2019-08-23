var app = getApp();
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '个人资料',
      'color': true,
      'class': '0'
    },
    userInfo:{},
    is_local: 1
  },
  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.getUserInfo();
    this.imageStorage();
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
   /**
   * 获取图片储存位置
   */
  imageStorage: function () {
    var that = this;
    app.baseGet(app.U({ c: "user_api", a: 'picture_storage_location' }), function (res) {
      that.setData({ is_local: res.data });
    });
  },
  getPhoneNumber:function(e){
    var detail = e.detail, cache_key = wx.getStorageSync('cache_key'),that=this;
    if (detail.errMsg =='getPhoneNumber:ok'){
      if (!cache_key){
        app.globalData.token='';
        app.globalData.isLog=false;
        return false;
      }
      app.basePost(app.U({ c: 'user_api', a: 'bind_mobile' }), { 
        iv: detail.iv,
        cache_key: cache_key, 
        encryptedData: detail.encryptedData
      },function(res){
        that.setData({ 'userInfo.phone': res.data.phone});
        return app.Tips({ title: res.msg, icon: 'success' });
      },function(res){
        return app.Tips({ title: '绑定失败'});
      });
    }else{
      app.Tips({ title:'取消授权'});
    }
  },

  /**
   * 获取用户详情
  */
  getUserInfo:function(){
    var that=this;
    app.baseGet(app.U({ c: 'user_api', a:'get_my_user_info'}),function(res){
      that.setData({userInfo:res.data});
    });
  },

  /**
  * 上传文件
  * 
 */
  uploadpic: function () {
    var that = this;
    util.uploadImageOne(app.U({ c: 'public_api', a: 'upload' }), function (res) {
      if (that.data.is_local == 1) {
        that.setData({ 'userInfo.avatar': app.globalData.url + res.data.url });
      } else {
        that.setData({ 'userInfo.avatar': res.data.url });
      }
    });
  },

  /**
   * 提交修改
  */
  formSubmit:function(e){
    var that = this, value = e.detail.value, formId = e.detail.formId;
    if (!value.nickname) return app.Tips({title:'用户姓名不能为空'});
    value.avatar = that.data.userInfo.avatar;
    app.basePost(app.U({ c: 'user_api', a: 'edit_user' }), value,function(res){
      return app.Tips({title:res.msg,icon:'success'},{tab:3,url:1});
    },function(res){
      return app.Tips({title:res.msg || '保存失败，您并没有修改'},{tab:3,url:1});
    });
  },

  

})