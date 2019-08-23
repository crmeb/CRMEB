// pages/apply-return/index.js
const app=getApp();
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '申请退货',
      'color': false
    },
    refund_reason_wap_img:[],
    orderInfo:{},
    RefundArray: [],
    index: 0,
    orderId:0,
  },
  /**
   * 授权回调
   * 
  */
  onLoadFun:function(){
    this.getOrderInfo();
    this.getRefundReason();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (!options.orderId) return app.Tips({title:'缺少订单id,无法退款'},{tab:3,url:1});
    this.setData({orderId:options.orderId});
  },
  /**
   * 获取订单详情
   * 
  */
  getOrderInfo:function(){
    var that=this;
    app.baseGet(app.U({ c: 'user_api', a: 'get_order', q: { uni:that.data.orderId}}),function(res){
      that.setData({orderInfo:res.data});
    });
  },
  /**
   * 获取退款理由
  */
  getRefundReason:function(){
    var that=this;
    app.baseGet(app.U({ c: 'public_api', a:'get_refund_reason'}),function(res){
      that.setData({ RefundArray:res.data});
    });
  },

  /**
   * 删除图片
   * 
  */
  DelPic:function(e){
    var index = e.target.dataset.index, that = this, pic = this.data.refund_reason_wap_img[index];
    app.basePost(app.U({ c: 'public_api', a: 'delete_image' }), { pic: pic.replace(app.globalData.url, '')},function(res){
      that.data.refund_reason_wap_img.splice(index,1);
      that.setData({ refund_reason_wap_img: that.data.refund_reason_wap_img });
    });
  },

  /**
   * 上传文件
   * 
  */
  uploadpic:function(){
    var that=this;
    util.uploadImageOne(app.U({ c: 'public_api', a:'upload'}),function(res){
      that.data.refund_reason_wap_img.push(res.data.url);
      that.setData({ refund_reason_wap_img: that.data.refund_reason_wap_img});
    });
  },


  /**
   * 申请退货
  */
  subRefund:function(e){
    var that = this, formId = e.detail.formId, value = e.detail.value;
    //收集form表单
    // if (!value.refund_reason_wap_explain) return app.Tips({title:'请输入退款原因'});
    app.baseGet(app.U({ c: 'public_api', a: 'get_form_id', q: { formId: formId}}),null,null,true);
    app.basePost(app.U({ c: 'auth_api', a:'apply_order_refund'}),{
      text: that.data.RefundArray[that.data.index] || '',
      refund_reason_wap_explain: value.refund_reason_wap_explain,
      refund_reason_wap_img: that.data.refund_reason_wap_img.join(','),
      uni: that.data.orderId
    },function(res){
      return app.Tips({ title: '申请成功', icon: 'success' },{tab:5,url:'/pages/user_return_list/index?isT=1'});
    },function(res){
      return app.Tips({title:res.msg});
    });
  },

  bindPickerChange: function (e) {
    this.setData({index: e.detail.value});
  },

})