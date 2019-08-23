var app = getApp();
Component({
  properties: {
    iShidden: {
      type: Boolean,
      value: true,
    },
    //是否自动登录
    isAuto: {
      type: Boolean,
      value: true,
    },
  },
  data: {
    cloneIner: null,
    loading:false,
  },
  pageLifetimes: {
    hide: function () {
      //关闭页面时销毁定时器
      if (this.data.cloneIner) clearInterval(this.data.cloneIner);
    },
    show: function () {
      //打开页面销毁定时器
      if (this.data.cloneIner) clearInterval(this.data.cloneIner);
    },
  },
  detached() {
    if (this.data.cloneIner) clearInterval(this.data.cloneIner);
  },
  attached() {
    this.get_logo_url();
    this.setAuthStatus();
  },
  methods: {
    get_logo_url: function () {
      if (wx.getStorageSync('logo_url')) return this.setData({ logo_url: wx.getStorageSync('logo_url') });
      app.baseGet(app.U({ c: 'public_api', a: 'get_logo_url' }), function (res) {
        wx.setStorageSync('logo_url', res.data.logo_url);
        this.setData({ logo_url: res.data.logo_url });
      }.bind(this));
    },
    //监听登录状态
    WatchIsLogin: function () {
      this.data.cloneIner = setInterval(function () {
        //防止死循环,超过错误次数终止监听
        if (this.getErrorCount()) return clearInterval(this.data.cloneIner);
        if (app.globalData.token == '' && this.data.loading===false) this.setAuthStatus();
      }.bind(this),800);
      this.setData({ cloneIner: this.data.cloneIner });
    },
    //检测登录状态并执行自动登录
    setAuthStatus() {
      var that = this;
      that.setErrorCount();
      wx.getSetting({
        success(res) {
          if (!res.authSetting['scope.userInfo']) {
            //没有授权不会自动弹出登录框
            if (that.data.isAuto === false) return;
            //自动弹出授权
            that.setData({ iShidden: false });
          } else {
            //自动登录
            that.setData({ iShidden: true });
            if (app.globalData.token) {
              that.triggerEvent('onLoadFun', app.globalData.token);
              that.WatchIsLogin();
            } else {
              wx.showLoading({ title: '正在登录中' });
              that.getUserInfoBydecryptCode();
            }
          }
        }
      })
    },
    //访问服务器获得cache_key
    setCode(code, successFn, errotFn) {
      var that = this;
      that.setData({ loading: true });
      app.basePost(app.U({ c: 'Login', a: 'setCode' }), { code: code }, function (res) {
        that.setData({ loading: false });
        wx.setStorage({ key: 'cache_key', data: res.data.cache_key});
        successFn && successFn(res);
      }, function (res) {
        that.setData({ loading: false });
        if (errotFn) errotFn(res);
        else return app.Tips({ title: '获取cache_key失败' });
      });
    },
    //获取code
    getSessionKey(code, successFn, errotFn) {
      var that = this;
      wx.checkSession({
        success: function (res) {
          wx.getStorage({
            key:'cache_key',
            success:function(res){
              if (res.data){
                successFn && successFn();
              }else{
                that.setCode(code, successFn, errotFn);
              }
            },
            fail(res){
              that.setCode(code, successFn, errotFn);
            },
          });
        },
        fail: function () {
          that.setCode(code, successFn, errotFn);
        }
      });
    },
    login:function(){
      var that=this;
      wx.login({
        success: function (res) {
          if (!res.code) return app.Tips({ title: '登录失败！' + res.errMsg });
          //获取cache_key并缓存
          that.getSessionKey(res.code, function () {
            that.getUserInfoBydecryptCode();
          });
        },
        fail() {
          wx.hideLoading();
        }
      })
    },
    //授权
    setUserInfo(e) {
      wx.showLoading({ title: '正在登录中' });
      this.login();
    },
    close: function () {
      if (this.data.isAuto) return;
      this.setData({ iShidden: true });
    },
    //登录获取访问权限
    getUserInfoBydecryptCode: function () {
      var that = this;
      if (this.getErrorCount()){
        this.setData({ iShidden: false, ErrorCount: 0 });
        return app.Tips({ title: '获取code失败,请重新授权尝试获取！' });
      } 
      wx.getStorage({
        key:'cache_key',
        success:function(res){
          if(res.data){
            var cache_key = res.data;
            wx.getUserInfo({
              lang: 'zh_CN',
              success: function (res) {
                var pdata = {};
                pdata.spid = app.globalData.spid;//获取推广人ID
                pdata.code = app.globalData.code;//获取推广人分享二维码ID
                if (res.iv) {
                  pdata.iv = encodeURI(res.iv);
                  pdata.encryptedData = res.encryptedData;
                  pdata.cache_key = cache_key;
                  //获取用户信息生成访问token
                  that.setData({ loading: true });
                  app.basePost(app.U({ c: 'login', a: 'index' }), pdata, function (res) {
                    that.setData({ loading: false });
                    if (res.data.status == 0) return app.Tips({ title: '抱歉，您已被禁止登录!' });
                    else if (res.data.status == 410) {
                      wx.clearStorage();
                      wx.hideLoading();
                      that.setErrorCount();
                      that.login();
                      return false;
                    }
                    //取消登录提示
                    wx.hideLoading();
                    //关闭登录弹出窗口
                    that.setData({ iShidden: true, ErrorCount: 0 });
                    //保存token和记录登录状态
                    app.globalData.token = res.data.token;
                    app.globalData.isLog = true;
                    //执行登录完成回调
                    that.triggerEvent('onLoadFun', app.globalData.uid);
                    //清除定时器
                    if (that.data.cloneIner) clearInterval(that.data.cloneIner);
                    //监听登录状态
                    that.WatchIsLogin();
                  }, function (res) {
                    that.setData({ loading: false });
                    wx.hideLoading();
                    that.setErrorCount();
                    wx.clearStorage();
                    return app.Tips({ title: res.msg });
                  });
                } else {
                  wx.hideLoading();
                  wx.clearStorage();
                  that.setErrorCount();
                  return app.Tips({ title: '用户信息获取失败!' });
                }
              },
              fail: function () {
                wx.hideLoading();
                wx.clearStorage();
                that.setErrorCount();
                if (that.data.isAuto) that.login();
              },
            })
          }else{
            wx.hideLoading();
            wx.clearStorage();
            that.setErrorCount();
            if (that.data.isAuto) that.login();
            return false;
          }
        },
        fail:function(){
          wx.hideLoading();
          wx.clearStorage();
          that.setErrorCount();
          if (that.data.isAuto) that.login();
        }
      })
    },
    /**
     * 处理错误次数,防止死循环
     * 
    */
    setErrorCount: function () {
      if (!this.data.ErrorCount) this.data.ErrorCount = 1;
      else this.data.ErrorCount++;
      this.setData({ ErrorCount: this.data.ErrorCount });
    },
    /**
     * 获取错误次数,是否终止监听
     * 
    */
    getErrorCount: function () {
      return this.data.ErrorCount >= 10 ? true : false;
    }
  },
})