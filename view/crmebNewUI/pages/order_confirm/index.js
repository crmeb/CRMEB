var app = getApp();
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    textareaStatus:true,
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '提交订单'
    },
    //支付方式
    cartArr: [
      { "name": "微信支付", "icon": "icon-weixin2", value: 'weixin', title: '微信快捷支付' },
      { "name": "余额支付", "icon": "icon-icon-test", value: 'yue',title:'可用余额:'},
    ],
    payType:'weixin',//支付方式
    openType:1,//优惠券打开方式 1=使用
    active:0,//支付方式切换
    coupon: { coupon: false, list: [], statusTile:'立即使用'},//优惠券组件
    address: {address: false},//地址组件
    addressInfo:{},//地址信息
    pinkId:0,//拼团id
    addressId:0,//地址id
    couponId:0,//优惠券id
    cartId:'',//购物车id
    userInfo:{},//用户信息
    mark:'',//备注信息
    couponTitle:'请选择',//优惠券
    coupon_price:0,//优惠券抵扣金额
    useIntegral:false,//是否使用积分
    integral_price:0,//积分抵扣金额
    ChangePrice:0,//使用积分抵扣变动后的金额
    formIds:[],//收集formid
    status:0,
    is_address:false,
    isClose:false,
    toPay:false,//修复进入支付时页面隐藏从新刷新页面
  },
  /**
   * 授权回调事件
   * 
  */
  onLoadFun:function(){
    this.getaddressInfo();
    this.getConfirm();
    //调用子页面方法授权后执行获取地址列表
    this.selectComponent('#address-window').getAddressList();
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({ textareaStatus: true });
    if (app.globalData.isLog && this.data.isClose && this.data.toPay==false) {
      this.getaddressInfo();
      this.selectComponent('#address-window').getAddressList();
    }
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({ isClose: true });
  },
  ChangCouponsClone:function(){
    this.setData({'coupon.coupon':false});
  },
  changeTextareaStatus:function(){
    for (var i = 0, len = this.data.coupon.list.length; i < len;i++){
      this.data.coupon.list[i].use_title='';
      this.data.coupon.list[i].is_use = 0;
    }
    this.setData({ textareaStatus: true, status: 0, "coupon.list": this.data.coupon.list});
  },
  /**
   * 处理点击优惠券后的事件
   * 
  */
  ChangCoupons:function(e){
    var index = e.detail, list = this.data.coupon.list, couponTitle = '请选择', couponId = 0, coupon_price = 0, totalPrice = 0, 
        change_coupon_price=0;
    for (var i = 0, len = list.length; i < len; i++) {
      if(i != index){
        list[i].use_title = '';
        list[i].is_use = 0;
      }
      //获取当前优惠券抵扣金额
      if (list[i].id == this.data.couponId) change_coupon_price = list[i].coupon_price;
    }
    if (this.data.totalPrice <= 0 && this.data.status != 1) return app.Tips({title:'支付金额为0无法使用优惠卷！'});
    if (this.data.status==1 || this.data.is_address) {
      this.setData({ totalPrice: this.data.priceGroup.totalPrice });
    }else{
      //使用优惠券抵扣前先把之前的抵扣金额加回去
      this.setData({ totalPrice: util.$h.Add(this.data.totalPrice, change_coupon_price) });
    }
    if (list[index].is_use) {
      //不使用优惠券
      list[index].use_title = '';
      list[index].is_use = 0;
      totalPrice = this.data.totalPrice;
      //用户取消使用优惠卷但是使用了积分抵扣
      if (totalPrice > 0 && this.data.useIntegral && !this.data.is_Integral) 
      {
        totalPrice = this.changeCouponPrice(totalPrice, this.data.userInfo.integral);
        this.setData({is_Integral:true});
      }
      this.data.status = 0;
    } else {
      //使用优惠券
      list[index].use_title = '不使用';
      list[index].is_use = 1;
      couponTitle = list[index].coupon_title;
      couponId = list[index].id;
      coupon_price = list[index].coupon_price;
     //使用积分抵扣,使用优惠券金额大于当前支付金额
      if (this.data.totalPrice < coupon_price && this.data.useIntegral){
        //超出金额
        var changePrice = util.$h.Sub(coupon_price, this.data.totalPrice);
        //超出回退积分
        var changeIntegral = util.$h.Div(changePrice, this.data.integralRatio);
        //回退积分和积分抵扣金额
        this.setData({ 
          integral: util.$h.Add(this.data.integral, changeIntegral), 
          integral_price: util.$h.Sub(this.data.integral_price, changePrice)
        });
        totalPrice=0;
        this.data.status=0;
      } else if (this.data.totalPrice < coupon_price && !this.data.useIntegral){
        //使用优惠券金额大于当前支付金额
        totalPrice=0;
        this.data.status=1;
      } else if (this.data.totalPrice > coupon_price && this.data.useIntegral){
        //支付金额大于优惠券金额并且使用了积分
        totalPrice = util.$h.Sub(this.data.totalPrice, list[index].coupon_price);
        //当前优惠券大于0的时候再去减去可兑换的金额
        if (this.data.integral > 0) totalPrice = this.changeCouponPrice(totalPrice, this.data.integral);
        this.data.status = 0;
      } else if (this.data.totalPrice > coupon_price && !this.data.useIntegral){
        //支付金额大于优惠券金额没有使用积分
        totalPrice = util.$h.Sub(this.data.totalPrice, list[index].coupon_price);
        this.data.status = 0;
      }
    }
    this.setData({ 
      couponTitle: couponTitle, 
      couponId: couponId, 
      'coupon.coupon': false, 
      "coupon.list":list,
      coupon_price: coupon_price,
      totalPrice: totalPrice,
      status: this.data.status,
    });
  },
  /**
   * 处理点击优惠券后支付金额和积分变动
   * @param string | float totalPrice 当前支付金额
   * @return float totalPrice 当前支付金额
   * 
  */
  changeCouponPrice: function (totalPrice, integral){
    var changePrice = util.$h.Mul(this.data.integralRatio,integral);
    this.data.integral_price=0;
    if (changePrice > totalPrice) {
      //超出金额
      var minParice = util.$h.Sub(changePrice, totalPrice);
      //超出积分
      var changeIntegral = util.$h.Div(minParice, this.data.integralRatio);
      //抵扣金额需要原本的订单金额
      this.setData({ integral: changeIntegral, integral_price: this.data.totalPrice });
      //超出金额当前支付金额为0
      totalPrice=0;
    } else {
      this.setData({ integral: 0, integral_price: util.$h.Add(this.data.integral_price, changePrice) });
      totalPrice = util.$h.Sub(totalPrice, changePrice);
    }
    return totalPrice;
  },
  /**
   * 使用积分抵扣
  */
  ChangeIntegral:function(){
    var integral=parseFloat(this.data.integral);
    if (this.data.userInfo.integral <= 0) return app.Tips({ title: '您当前积分为较低不能使用抵扣' }, function () { 
      this.setData({ useIntegral:false });
    }.bind(this));
    if (this.data.totalPrice <= 0 && !this.data.useIntegral) return app.Tips({title:'当前支付金额不能在使用积分抵扣啦~'},function(){
      this.setData({ useIntegral: false });
    }.bind(this));
    this.setData({useIntegral:!this.data.useIntegral});
    //使用积分抵扣时
    if (this.data.useIntegral){
      var changePrice = util.$h.Mul(this.data.integralRatio, integral);
      if (changePrice > this.data.totalPrice){
        //超出金额
        var minParice = util.$h.Sub(changePrice, this.data.totalPrice);
        //超出积分
        var changeIntegral = util.$h.Div(minParice, this.data.integralRatio);
        //超出当前金额支付金额为0,积分抵扣金额为当前支付金额,积分剩余等于超出积分
        this.setData({ integral: changeIntegral, integral_price:this.data.totalPrice,totalPrice:0});
      }else{
        this.setData({ integral: 0, integral_price: changePrice, totalPrice: util.$h.Sub(this.data.totalPrice, changePrice)});
      }
    }else{
      var integral_price = this.data.integral_price;
      //不使用积分返回原始数据
      this.setData({ integral_price: 0, integral: this.data.userInfo.integral, totalPrice: util.$h.Add(this.data.totalPrice, integral_price.toString())});
    }
  },
  /**
   * 选择地址后改变事件
   * @param object e
  */
  OnChangeAddress:function(e){
    this.setData({ textareaStatus:true,addressId: e.detail,'address.address':false});
    this.getaddressInfo();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (!options.cartId) return app.Tips({ title:'请选择要购买的商品'},{tab:3,url:1});
    this.setData({ 
      couponId: options.couponId || 0, 
      pinkId: options.pinkId ? parseInt(options.pinkId) : 0, 
      addressId: options.addressId || 0, 
      cartId: options.cartId,
      is_address: options.is_address ? true : false,
    });
  },
  bindHideKeyboard: function (e) {
    this.setData({mark: e.detail.value});
  },
  /**
   * 获取当前订单详细信息
   * 
  */
  getConfirm:function(){
    var that=this;
    app.basePost(app.U({ c: 'auth_api', a: 'confirm_order' }), { cartId: this.data.cartId},function(res){
      that.setData({
        userInfo: res.data.userInfo,
        integral: res.data.userInfo.integral,
        cartInfo: res.data.cartInfo,
        integralRatio: res.data.integralRatio,
        offlinePostage: res.data.offlinePostage,
        orderKey: res.data.orderKey,
        priceGroup: res.data.priceGroup,
        totalPrice: app.help().Add(parseFloat(res.data.priceGroup.totalPrice), parseFloat(res.data.priceGroup.storePostage)),
        cartId: res.data.cartId,
        seckillId: parseInt(res.data.seckill_id),
        usableCoupon: res.data.usableCoupon
      });
      that.data.cartArr[1].title ='可用余额:'+ res.data.userInfo.now_money;
      that.setData({ cartArr: that.data.cartArr, ChangePrice:that.data.totalPrice});
      that.getBargainId();
      that.getCouponList();
    },function(res){
      return app.Tips({title:res.msg},{tab:3,url:1});
    });
  },
  /*
  * 提取砍价和拼团id
  */
  getBargainId: function () {
    var that = this;
    var cartINfo = that.data.cartInfo;
    var BargainId = 0;
    var combinationId = 0;
    cartINfo.forEach(function (value, index, cartINfo) {
      BargainId = cartINfo[index].bargain_id,
        combinationId = cartINfo[index].combination_id
    })
    that.setData({ BargainId: parseInt(BargainId), combinationId: parseInt(combinationId)});
  },
  /**
   * 获取当前金额可用优惠券
   * 
  */
  getCouponList:function(){
    var that=this;
    app.baseGet(app.U({ c: "coupons_api", a: 'get_use_coupon_order', q: { totalPrice:this.data.totalPrice }}),function(res){
      that.setData({ 'coupon.list': res.data, openType:1});
    });
  },
  /*
  * 获取默认收货地址或者获取某条地址信息
  */
  getaddressInfo:function(){
    var that=this;
    var url = that.data.addressId ? 
    app.U({ c: 'user_api', a: 'get_user_address', q: { addressId: that.data.addressId } }) : 
    app.U({ c: 'user_api', a:'user_default_address'}); 
    app.baseGet(url,function(res){
      res.data.is_default = parseInt(res.data.is_default);
      that.setData({ addressInfo: res.data || {}, addressId: res.data.id || 0, 'address.addressId': res.data.id || 0});
    });
  },
  payItem:function(e){
    var that = this;
    var active = e.currentTarget.dataset.index;
    that.setData({
      active: active,
      animated: true,
      payType: that.data.cartArr[active].value,
    })
    setTimeout(function () {
      that.car();
    }, 500);
  },
  coupon: function () {
    this.setData({
      'coupon.coupon': true
    })
  },
  car: function () {
    var that = this;
    that.setData({
      animated: false
    });
  },
  address:function(){
    this.setData({
      textareaStatus:false,
      'address.address': true,
      pagesUrl: '/pages/user_address_list/index?cartId=' + this.data.cartId + '&pinkId=' + this.data.pinkId + '&couponId=' + this.data.couponId
    });
  },
  SubOrder:function(e){
    var formId = e.detail.formId, that = this, data={};
    if (!this.data.payType) return app.Tips({title:'请选择支付方式'});
    if (!this.data.addressId) return app.Tips({ title:'请选择收货地址'});
    data={
      addressId: that.data.addressId,
      formId: formId,
      couponId: that.data.couponId,
      payType: that.data.payType,
      useIntegral: that.data.useIntegral,
      bargainId: that.data.BargainId,
      combinationId: that.data.combinationId,
      pinkId: that.data.pinkId,
      seckill_id: that.data.seckillId,
      mark: that.data.mark
    };
    if (data.payType == 'yue' && parseFloat(that.data.userInfo.now_money) < parseFloat(that.data.totalPrice)) return app.Tips({title:'余额不足！'});
    wx.showLoading({ title: '订单支付中'});
    app.basePost(app.U({ c: 'auth_api', a:'create_order',q:{key:this.data.orderKey}}),data,function(res){
      var status = res.data.status, orderId = res.data.result.orderId, jsConfig = res.data.result.jsConfig, 
        goPages = '/pages/order_pay_status/index?order_id=' + orderId+'&msg='+res.msg;
      switch (status){
        case 'ORDER_EXIST': case 'EXTEND_ORDER': case 'PAY_ERROR':
            wx.hideLoading();
            return app.Tips({ title: res.msg}, { tab: 5, url: goPages });
          break;
        case 'SUCCESS':
            wx.hideLoading();
          if (that.data.BargainId || that.data.combinationId || that.data.pinkId || that.data.seckillId) return app.Tips({ title: res.msg, icon: 'success' }, { tab: 4, url: goPages });
            return app.Tips({ title: res.msg,icon: 'success' }, { tab: 5, url: goPages });
          break;
        case 'WECHAT_PAY':
          that.setData({toPay:true});
          wx.requestPayment({
            timeStamp: jsConfig.timestamp,
            nonceStr: jsConfig.nonceStr,
            package: jsConfig.package,
            signType: jsConfig.signType,
            paySign: jsConfig.paySign,
            success: function (res) {
              wx.hideLoading();
              if (that.data.BargainId || that.data.combinationId || that.data.pinkId || that.data.seckillId) return app.Tips({ title: '支付成功', icon: 'success' }, { tab: 4, url: goPages });
              return app.Tips({ title: '支付成功', icon:'success' }, { tab: 5, url: goPages });
            },
            fail:function(e){
              wx.hideLoading();
              return app.Tips({ title: '取消支付' }, { tab: 5, url: goPages +'&status=2'});
            },
            complete:function(e){
              wx.hideLoading();
              //关闭当前页面跳转至订单状态
              if (res.errMsg == 'requestPayment:cancel') return app.Tips({ title: '取消支付' }, { tab: 5, url: goPages + '&status=2'});
            },
          })
          break;
        case 'PAY_DEFICIENCY':
          wx.hideLoading();
          //余额不足
          return app.Tips({ title: res.msg}, { tab: 5, url: goPages+'&status=1' });
          break;
      }
    });
    
  }
})