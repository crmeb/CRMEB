//app.js

//请配置请求url
//请修改开发者工具中【详情】->【AppID】改为自己的Appid
//请前往后台【小程序】->【小程序配置】填写自己的 appId and AppSecret
const URL ='http://activity.crmeb.net';

const util = require('utils/util.js');

App({
  onLaunch: function (option) {
    if (URL==''){
      console.error("请配置请求url\n请修改开发者工具中【详情】->【AppID】改为自己的Appid\n请前往后台【小程序】->【小程序配置】填写自己的 appId and AppSecret");
      return false;
    }
    var that = this;
    if (option.query.hasOwnProperty('scene')){
      switch (option.scene) {
        //扫描小程序码
        case 1047:
          that.globalData.code = option.query.scene;
          break;
        //长按图片识别小程序码
        case 1048:
          that.globalData.code = option.query.scene;
          break;
        //手机相册选取小程序码
        case 1049:
          that.globalData.code = option.query.scene;
          break;
        //直接进入小程序
        case 1001:
          that.globalData.spid = option.query.scene;
          break;
      }
    }
    this.getMyMenus();
    // 获取导航高度；
    wx.getSystemInfo({
      success: res => {
        //导航高度
        this.globalData.navHeight = res.statusBarHeight * (750 / res.windowWidth) + 97;
      }, fail(err) {
        console.log(err);
      }
    })
  },
  globalData: {
    navHeight: 0,
    routineStyle: '#ffffff',
    openPages: '',
    spid: 0,
    code:0,
    urlImages: '',
    url: URL,
    token: '',
    isLog:false,
    MyMenus:[],
    header: {
      'content-type': 'application/json',
      'token': '' 
    }
  },
  /**
   * 
   * 获取个人中心图标
  */
  getMyMenus: function () {
    var that = this;
    if (that.globalData.MyMenus.legnth) return;
    that.baseGet(that.U({ c: 'public_api', a: 'get_my_naviga'},that.globalData.url), function (res) {
      that.globalData.MyMenus = res.data.routine_my_menus;
    });
  },
  /*
  * POST 访问快捷方法
  * @param string | object url 访问地址
  * @param callable successCallback 成功执行函数
  * @param callable errorCallback 失败执行函数
  * @param object header 访问header头
  */
  basePost: function (url, data, successCallback, errorCallback, header) {
    if (header == undefined) header = this.globalData.header;
    header['token'] = this.globalData.token;
    util.basePost(url, data, successCallback, errorCallback,header);
  },
  /*
  * GET 访问快捷方法
  * @param string | object url 访问地址
  * @param callable successCallback 成功执行函数
  * @param callable errorCallback 失败执行函数
  * @param isMsg 错误信息提醒 默认提醒
  * @param object header 访问header头
  */
  baseGet: function (url, successCallback, errorCallback, isMsg, header) {
    if (header == undefined) header = this.globalData.header;
    header['token'] = this.globalData.token;
    util.baseGet(url, successCallback, errorCallback, isMsg,header);
  },
  /*
  * 信息提示 + 跳转
  * @param object opt {title:'提示语',icon:''} | url
  * @param object to_url 跳转url 有5种跳转方式 {tab:1-5,url:跳转地址}
  */
  Tips: function (opt, to_url) { 
    return util.Tips(opt, to_url);
  },
  /*
  * 访问Url拼接
  * @param object opt {c:'控制器',a:'方法',q:{get参数},p:{parma参数}}
  * @param url 接口访问地址
  * @return string
  */
  U: function (opt,url){ 
    return util.U(opt,url);
  },
  /**
   * 快捷调取助手函数
  */
  help:function()
  {
    return util.$h;
  },
  /*
  * 合并数组
  * @param array list 请求返回数据
  * @param array sp 原始数组
  * @return array
  */
  SplitArray: function (list, sp) { return util.SplitArray(list, sp)},
})