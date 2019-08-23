// pages/product-con/index.js
var app = getApp();
var wxh = require('../../utils/wxh.js');
var util = require('../../utils/util.js');
var WxParse = require('../../wxParse/wxParse.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '商品详情'
    },
    attribute:{'cartAttr':false},//属性是否打开
    coupon: {
      'coupon': false,
      list:[],
    },
    attr:'请选择',//属性页面提示
    attrValue:'',//已选属性
    animated: false,//购物车动画
    id:0,//商品id
    replyCount: 0,//总评论数量
    reply: [],//评论列表
    storeInfo: {},//商品详情
    productAttr: [],//组件展示属性
    productValue: [],//系统属性
    couponList: [],   //优惠券
    productSelect: {}, //属性选中规格
    cart_num: 1,//购买数量
    isAuto: false,//没有授权的不会自动授权
    iShidden:true,//是否隐藏授权
    isOpen:false,//是否打开属性组件
    isLog:app.globalData.isLog,//是否登录
    actionSheetHidden:true,
    posterImageStatus:false,
    storeImage: '',//海报产品图
    PromotionCode: '',//二维码图片
    canvasStatus: false,//海报绘图标签
    posterImage: '',//海报路径
    posterbackgd:'/images/posterbackgd.png',
    sharePacket:{
      isState:true,//默认不显示
    },//分销商详细
    uid:0,//用户uid
  },
  /**
   * 登录后加载
   * 
  */
  onLoadFun:function(e){
    this.setData({ isLog:true});
    this.getCouponList();
    this.getCartCount();
    this.downloadFilePromotionCode();
    this.getUserInfo();
    this.get_product_collect();
  },
  ChangCouponsClone:function(){
    this.setData({'coupon.coupon':false});
  },
  /*
  * 获取用户信息
  */
  getUserInfo: function(){
    var that=this;
    app.baseGet(app.U({ c: 'user_api', a:'get_my_user_info'}),function(res){
      that.setData({ 'sharePacket.isState': res.data.is_promoter ? false : true, uid: res.data.uid});
    });
  },
  /**
   * 购物车数量加和数量减
   * 
  */
  ChangeCartNum:function(e){
    //是否 加|减
    var changeValue = e.detail;
    //获取当前变动属性
    var productSelect = this.data.productValue[this.data.attrValue];
    //如果没有属性,赋值给商品默认库存
    if (productSelect === undefined && !this.data.productAttr.length) productSelect = this.data.productSelect;
    //不存在不加数量
    if (productSelect===undefined) return;
    //提取库存
    var stock = productSelect.stock || 0;
    //设置默认数据
    if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
    //数量+
    if (changeValue){
      productSelect.cart_num++;
      //大于库存时,等于库存
      if (productSelect.cart_num > stock) productSelect.cart_num = stock;
      this.setData({
        ['productSelect.cart_num']: productSelect.cart_num,
        cart_num: productSelect.cart_num
      });
    }else{
      //数量减
      productSelect.cart_num--;
      //小于1时,等于1
      if (productSelect.cart_num < 1) productSelect.cart_num=1;
      this.setData({
        ['productSelect.cart_num']: productSelect.cart_num,
        cart_num: productSelect.cart_num
      });
    }
  },
  /**
   * 属性变动赋值
   * 
  */
  ChangeAttr:function(e){
    var values = e.detail;
    var productSelect = this.data.productValue[values];
    var storeInfo = this.data.storeInfo;
    if (productSelect){
      this.setData({
        ["productSelect.image"]: productSelect.image,
        ["productSelect.price"]: productSelect.price,
        ["productSelect.stock"]: productSelect.stock,
        ['productSelect.unique']: productSelect.unique,
        ['productSelect.cart_num']: 1,
        attrValue: values,
        attr:'已选择'
      });
    }else{
      this.setData({
        ["productSelect.image"]: storeInfo.image,
        ["productSelect.price"]: storeInfo.price,
        ["productSelect.stock"]: 0,
        ['productSelect.unique']:'',
        ['productSelect.cart_num']: 0,
        attrValue:'',
        attr:'请选择'
      });
    }
  },
  /**
   * 领取完毕移除当前页面领取过的优惠券展示
  */
  ChangCoupons:function(e){
    var coupon = e.detail;
    var couponList = util.ArrayRemove(this.data.couponList, 'id', coupon.id);
    this.setData({ couponList: couponList});
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    //扫码携带参数处理
    if (options.scene){
      var value =util.getUrlParams(decodeURIComponent(options.scene));
      if (value.id) options.id = value.id;
      //记录推广人uid
      if (value.pid) app.globalData.spid = value.pid;
    }
    if (!options.id) return app.Tips({ title: '缺少参数无法查看商品' }, { tab: 3, url: 1 });
    this.setData({ id: options.id});
    //记录推广人uid
    if (options.spid) app.globalData.spid=options.spid;
    this.getGoodsDetails();
  },
  /**
   * 获取产品详情
   * 
  */
  getGoodsDetails:function(){
    var that=this;
    app.baseGet(app.U({ c: 'store_api', a:'details',q:{id:that.data.id}}),function(res){
      var storeInfo = res.data.storeInfo;
      that.setData({
        storeInfo: storeInfo,
        reply: res.data.reply ? [res.data.reply] : [],
        replyCount: res.data.replyCount,
        description: storeInfo.description,
        replyChance: res.data.replyChance,
        productAttr: res.data.productAttr,
        productValue: res.data.productValue,
        ["sharePacket.priceName"]: res.data.priceName,
        ['parameter.title']: storeInfo.store_name
      });
      that.downloadFilestoreImage();
      that.DefaultSelect();
      //html转wxml
      WxParse.wxParse('description', 'html', that.data.description, that, 0);
    },function(res){
      //状态异常返回上级页面
      return app.Tips({ title: res.msg }, { tab: 3, url: 1 });
    });
  },
  /**
   * 默认选中属性
   * 
  */
  DefaultSelect:function(){
    var productAttr = this.data.productAttr, storeInfo = this.data.storeInfo;
    for (var i = 0, len = productAttr.length;i < len; i++){
      if (productAttr[i].attr_value[0]) productAttr[i].checked = productAttr[i].attr_value[0]['attr'];
    }
    var value=this.data.productAttr.map(function (attr) {
      return attr.checked;
    });
    var productSelect = this.data.productValue[value.sort().join(',')];
    if (productSelect){
      this.setData({
        ["productSelect.store_name"]: storeInfo.store_name,
        ["productSelect.image"]: productSelect.image,
        ["productSelect.price"]: productSelect.price,
        ["productSelect.stock"]: productSelect.stock,
        ['productSelect.unique']: productSelect.unique,
        ['productSelect.cart_num']: 1,
        attrValue: value,
        attr: '已选择'
      });
    }else{
      this.setData({
        ["productSelect.store_name"]:storeInfo.store_name,
        ["productSelect.image"]: storeInfo.image,
        ["productSelect.price"]: storeInfo.price,
        ["productSelect.stock"]: this.data.productAttr.length ? 0 : storeInfo.stock ,
        ['productSelect.unique']:  '',
        ['productSelect.cart_num']: 1,
        attrValue: '',
        attr: '请选择'
      });
    }
    this.setData({ productAttr: productAttr});
  },
  /**
   * 获取是否收藏
  */
  get_product_collect:function(){
    var that=this;
    app.baseGet(app.U({ c: 'store_api', a: 'get_product_collect', q: { product_id:that.data.id}}),function(res){
      that.setData({ 'storeInfo.userCollect': res.data.userCollect});
    });
  },
  /**
   * 获取优惠券
   * 
  */
  getCouponList(){
    var that=this;
    app.baseGet(app.U({ c: "coupons_api", a:'get_issue_coupon_list',q:{limit:10}}),function(res){
      var couponList=[];
      for (var i = 0; i < res.data.length;i++){
        if (!res.data[i].is_use && couponList.length < 2) couponList.push(res.data[i]);
      }
      that.setData({
        ['coupon.list']:res.data,
        couponList: couponList
      });
    });
  },
  /** 
   * 
   * 
  * 收藏商品
  */
  setCollect:function(){
    if (app.globalData.isLog === false) {
      this.setData({
        isAuto: true,
        iShidden: false,
      });
    } else {
      var url =app.U({ 
        c: 'store_api', 
        a: this.data.storeInfo.userCollect ? 'uncollect_product' : 'collect_product', 
        q: { productId:this.data.storeInfo.id}
      }),that=this;
      app.baseGet(url,function(res){
        that.setData({
          ['storeInfo.userCollect']: !that.data.storeInfo.userCollect
        })
      });
    }
  },
  /**
   * 打开属性插件
  */
  selecAttr:function(){
    if (app.globalData.isLog===false)
      this.setData({ isAuto: true,iShidden:false})
    else
      this.setData({ 'attribute.cartAttr': true,isOpen:true})
  },
  /**
   * 打开优惠券插件
  */
  coupon:function(){
    if (app.globalData.isLog === false)
      this.setData({isAuto: true,iShidden: false})
    else{
      this.getCouponList();
      this.setData({ 'coupon.coupon': true })
    }
  },
  onMyEvent: function (e) {
    this.setData({ 'attribute.cartAttr': e.detail.window, isOpen:false})
  },
  /**
   * 打开属性加入购物车
   * 
  */
  joinCart:function(e){
    var formId = e.detail.formId;
    //是否登录
    if (app.globalData.isLog === false)
      this.setData({isAuto: true,iShidden: false,});
    else{
      app.baseGet(app.U({ c: 'public_api', a: 'get_form_id', q: { formId: formId } }), null, null, true);
      this.goCat();
    }
  },
  /*
  * 加入购物车
  */
  goCat: function (isPay, is_new){
    var that=this;
    var productSelect = this.data.productValue[this.data.attrValue];
    //打开属性
    if (this.data.attrValue){
      //默认选中了属性，但是没有打开过属性弹窗还是自动打开让用户查看默认选中的属性
      this.setData({ 'attribute.cartAttr': !this.data.isOpen ? true : false }) 
    }else{
      if (this.data.isOpen) 
        this.setData({ 'attribute.cartAttr': true }) 
      else 
        this.setData({ 'attribute.cartAttr': !this.data.attribute.cartAttr});
    }
    //只有关闭属性弹窗时进行加入购物车
    if (this.data.attribute.cartAttr === true && this.data.isOpen == false) return this.setData({ isOpen: true }); 
    //如果有属性,没有选择,提示用户选择
    if (this.data.productAttr.length && productSelect === undefined && this.data.isOpen==true) return app.Tips({title:'请选择属性'});
    app.baseGet(app.U({ 
      c: 'auth_api',
      a: isPay == undefined ? 'set_cart' : 'now_buy', 
      q:{ 
        productId: that.data.id, 
        cartNum: that.data.cart_num,
        uniqueId: productSelect !== undefined ? productSelect.unique : '',
        is_new: is_new === undefined ? 0 : 1,
        }
      }),function(res){
        that.setData({ isOpen: false,'attribute.cartAttr':false});
        if (isPay)
          wx.navigateTo({url: '/pages/order_confirm/index?cartId=' + res.data.cartId});
        else
          app.Tips({ title: '添加购物车成功', icon: 'success' },function(){
            that.getCartCount(true);
          });
    })
  },
  /**
   * 获取购物车数量
   * @param boolean 是否展示购物车动画和重置属性
  */
  getCartCount: function (isAnima) {
    var that = this;
    app.baseGet(app.U({ c: 'auth_api', a:'get_cart_num'}),function(res){
      that.setData({CartCount: res.data});
       //加入购物车后重置属性
      if (isAnima){
        that.setData({
          animated: true,
          attrValue: '',
          attr: '请选择',
          ["productSelect.image"]: that.data.storeInfo.image,
          ["productSelect.price"]: that.data.storeInfo.price,
          ["productSelect.stock"]: that.data.storeInfo.stock,
          ['productSelect.unique']: '',
          ['productSelect.cart_num']: 1,
        });
        that.selectComponent('#product-window').ResetAttr();
        setTimeout(function () {
          that.setData({
            animated: false
          });
        }, 500);
      }
    });
  },
  /**
   * 立即购买
  */
  goBuy:function(e){
    var that = this,formId = e.detail.formId;
    if (app.globalData.isLog === false)
      this.setData({ isAuto: true, iShidden: false });
    else{
      app.baseGet(app.U({ c: 'public_api', a: 'get_form_id', q: { formId: formId } }), null, null, true);
      this.goCat(true,1);
    }
  },
  /**
   * 分享打开和关闭
   * 
  */
  listenerActionSheet: function () {
    if (app.globalData.isLog === false)
      this.setData({ isAuto: true, iShidden: false });
    else
      this.setData({ actionSheetHidden: !this.data.actionSheetHidden })
  },
  //隐藏海报
  posterImageClose: function () {
    this.setData({posterImageStatus: false,})
  },
  //替换安全域名
  setDomain: function (url) {
    url = url ? url.toString() : '';
    //本地调试打开,生产请注销
    // return url;
    if (url.indexOf("https://") > -1) return url;
    else return url.replace('http://', 'https://');
  },
  //获取海报产品图
  downloadFilestoreImage: function () {
    var that = this;
    wx.downloadFile({
      url: that.setDomain(that.data.storeInfo.image),
      success: function (res) {
        that.setData({
          storeImage: res.tempFilePath
        })
      },
      fail:function(){
        return app.Tips({title:''});
        that.setData({
          storeImage: '',
        })
      },
    });
  },
  /**
   * 获取产品分销二维码
   * @param function successFn 下载完成回调
   * 
  */
  downloadFilePromotionCode: function (successFn) {
    var that = this;
    app.baseGet(app.U({ c: 'store_api', a:'product_promotion_code',q:{id:this.data.id}}),function(res){
      wx.downloadFile({
        url: that.setDomain(res.msg),
        success: function (res) { 
        if (typeof successFn == 'function')
          successFn && successFn(res.tempFilePath);
        else
          that.setData({PromotionCode: res.tempFilePath}); 
        }, 
        fail:function(){
          that.setData({ PromotionCode: '' });
        },
      });
    }, function (res) { that.setData({ PromotionCode: '' }); });
  },
  /**
   * 生成海报
  */
  goPoster:function(){
    var that = this;
    that.setData({ canvasStatus: true });
    var arr2 = [that.data.posterbackgd, that.data.storeImage, that.data.PromotionCode];
    wx.getImageInfo({
      src: that.data.PromotionCode,
      fail: function (res) {
        return app.Tips({ 'title': '小程序二维码需要发布正式版后才能获取到' });
      },
    });
    if (arr2[2] == ''){
      //海报二维码不存在则从新下载
      that.downloadFilePromotionCode(function (msgPromotionCode){
        arr2[2] = msgPromotionCode;
        if (arr2[2]=='') return app.Tips({title:'海报二维码生成失败！'});
        util.PosterCanvas(arr2, that.data.storeInfo.store_name, that.data.storeInfo.price, function (tempFilePath){
          that.setData({
            posterImage:tempFilePath,
            posterImageStatus: true,
            canvasStatus: false,
            actionSheetHidden: !that.data.actionSheetHidden
          })
        });
      });
    }else{
      //生成推广海报
      util.PosterCanvas(arr2, that.data.storeInfo.store_name, that.data.storeInfo.price, function (tempFilePath) {
        that.setData({
          posterImage: tempFilePath,
          posterImageStatus: true,
          canvasStatus: false,
          actionSheetHidden: !that.data.actionSheetHidden
        })
      });
    }
  },
  /*
  * 保存到手机相册
  */
  savePosterPath: function () {
    var that = this;
    wx.getSetting({
      success(res) {
        if (!res.authSetting['scope.writePhotosAlbum']) {
          wx.authorize({
            scope: 'scope.writePhotosAlbum',
            success() {
              wx.saveImageToPhotosAlbum({
                filePath: that.data.posterImage,
                success: function (res) {
                  that.posterImageClose();
                  app.Tips({ title: '保存成功', icon:'success'});
                },
                fail: function (res) {
                  app.Tips({ title: '保存失败' });
                }
              })
            }
          })
        } else {
          wx.saveImageToPhotosAlbum({
            filePath: that.data.posterImage,
            success: function (res) {
              that.posterImageClose();
              app.Tips({ title: '保存成功', icon: 'success' });
            },
            fail: function (res) {
              app.Tips({ title: '保存失败' });
            },
          })
        }
      }
    })
  },
  set_user_share:function(){
    app.baseGet(app.U({ c: 'public_api', a:'set_user_share'}),null,null,true);
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    var that = this;
    that.setData({actionSheetHidden: !that.data.actionSheetHidden});
    that.set_user_share();
    return {
      title: that.data.productSelect.store_name,
      imageUrl: that.data.productSelect.image,
      path: '/pages/goods_details/index?id=' + that.data.id + '&spid='+that.data.uid,
    }
  }
})