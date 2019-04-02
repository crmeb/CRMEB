var app = getApp();
// var wxh = require('../../utils/wxh.js');
App({
  onLaunch: function (option) {
    // 展示本地存储能力
    var that = this; 
    if (option.query.hasOwnProperty('scene')) {
      var sceneCode = option.query.scene.split('-');
      if (sceneCode.length == 1) { //分享链接进入获取推广人二维码ID
        that.globalData.spreadid = sceneCode[0];
      } else if (sceneCode.length == 2) { //扫码进入进入获取推广人二维码ID
        that.globalData.spreadid = sceneCode[0];
        if (sceneCode[1]){
          wx.reLaunch({ url: option.path + '?id=' + sceneCode[1] });
        } 
      }
    }
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)
    that.getRoutineConfig();
  },
  globalData: {
    config:null,//小程序相关配置
    uid: null,
    uidShare:null,//我的推广二维码ID
    openid:'',
    openPages:'',//记录要访问的页面
    spreadid: 0,//推广人二维码ID 2.5.36
    spid:0,//推广人二维码ID
    urlImages: '',
    url: 'https://shop.crmeb.net/',
  },
  //获取小程序配置
  getRoutineConfig:function(){
    var that = this;
    wx.request({
      url: that.globalData.url + '/routine/logins/get_routine_config',
      method: 'post',
      dataType  : 'json',
      success: function (res) {
        if(res.data.code == 200 ){
          that.globalData.config = res.data.data.routine_config;
          that.setBarColor('#FFFFFF');
        }else{
          wx.showToast({
            title: '请求接口错误',
            icon: 'none',
            duration: 1500,
          })
        }
      },
      fail:function(){
        wx.showToast({
          title: '配置信息获取失败',
          icon: 'none',
          duration: 1500,
        })
      }
    })
  },
  setBarColor: function (routine_style = '#FFFFFF'){
    var that = this;
      wx.setNavigationBarColor({
        frontColor: '#000000',
        backgroundColor: routine_style,
      })
  },
  setUserInfo : function(){
    var that = this;
    if (that.globalData.uid == null) {//是否存在用户信息，如果不存在跳转到首页
      setTimeout(function () {
        wx.navigateTo({
          url: '/pages/loading/loading',
        })
      }, 1500)
    }
  },
  rp: function (n) {
    var cnum = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
    var s = '';
    n = '' + n; // 数字转为字符串
    for (var i = 0; i < n.length; i++) {
      s += cnum[parseInt(n.charAt(i))];
    }
    return s;
  },
  U: function (opt) {
    var m = opt.m || 'routine', c = opt.c || 'auth_api', a = opt.a || 'index', q = opt.q || '',
      p = opt.p || {}, params = '', gets = '';
    params = Object.keys(p).map(function (key) {
      return key + '/' + p[key];
    }).join('/');
    gets = Object.keys(q).map(function (key) {
      return key + '=' + q[key];
    }).join('&');
    return this.globalData.url + '/' + m + '/' + c + '/' + a + '/uid/' + this.globalData.uid + (params == '' ? '' : '/' + params) + (gets == '' ? '' : '?' + gets) + '&uid='+this.globalData.uid;
  },
  baseGet: function (url, successCallback, errorCallback) {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded'
    };
    wx.request({
      url: url,
      method: 'get',
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
          successCallback && successCallback(res.data);
        } else {
          errorCallback && errorCallback(res.data);
          that.Tips({ title: res.data.msg });
        }
      }, fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {

      }
    });
  },
  Tips: function (opt, to_url) {
    var title = opt.title || '', icon = opt.icon || 'none', endtime = opt.endtime || 2000;
    wx.showToast({
      title: title,
      icon: 'none',
      duration: endtime,
    })
    if (to_url != undefined) {
      if (typeof to_url == 'object') {
        var tab = to_url.tab || 1, url = to_url.url || '';
        switch (tab) {
          case 1:
            //一定时间后跳转至 table
            setTimeout(function () {
              wx.switchTab({
                url: url
              })
            }, endtime);
            break;
          case 2:
            //跳转至非table页面
            setTimeout(function () {
              wx.navigateTo({
                url: url,
              })
            }, endtime);
            break;
          case 3:
            //返回上页面
            setTimeout(function () {
              wx.navigateBack({
                delta: parseInt(url),
              })
            }, endtime);
            break;
          case 4:
            //关闭当前所有页面跳转至非table页面
            setTimeout(function () {
              wx.reLaunch({
                url: url,
              })
            }, endtime);
            break;
          case 5:
            //关闭当前页面跳转至非table页面
            setTimeout(function () {
              wx.redirectTo({
                url: url,
              })
            }, endtime);
            break;
        }
      } else {
        setTimeout(function () {
          wx.navigateTo({
            url: to_url,
          })
        }, endtime);
      }
    }
  },
})