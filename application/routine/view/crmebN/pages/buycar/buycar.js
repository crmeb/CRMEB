// import bm from '../../utils/baseMath.js'
// // pages/buycar/buycar.js
var app = getApp();
// // var wx = require('../../utils/wx.js');

Page({
  data: {
    isAttrInfo:'attrInfo',
    itemAttrInfo : '',
    foothidden:false,
    url: app.globalData.urlImages,
    countmoney:"",
    cartNum:"",
    isAllSelect:false,
    minusStatus: 'disabled',
    cartList:[],
    cartInvalid:[],
    cartIdsStr:''
  },

  setNumber: function (e) {
    var that = this;
    var index = e.currentTarget.dataset.item;
    var cartList = that.data.cartList;
    var num = parseInt(e.detail.value);
    var cart_num = num ? num : 1;
    if (cartList[index].productInfo.attrInfo.stock){
      if (cart_num > cartList[index].productInfo.stock) cart_num = cartList[index].productInfo.attrInfo.stock
    }else{
      if (cart_num > cartList[index].productInfo.attrInfo.stock) cart_num = cartList[index].productInfo.stock
    }
    cartList[index].cart_num = cart_num;
    that.setData({
      cartList: cartList
    })
    wx.request({
      url: app.globalData.url + '/routine/auth_api/set_buy_cart_num?uid=' + app.globalData.uid,
      method: 'GET',
      data:{
        cartId: cartList[index].id,
        cartNum: cart_num
      },
      success: function (res) {
        if (res.data.code == 400) {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 1000,
          })
        }
      }
    })
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.carnum();
    this.countmoney();
    this.getList();
  },
  btntap: function (e) {
    this.data.foothidden = !this.data.foothidden;
    this.setData({
      foothidden: this.data.foothidden
    })
  },
  getList:function(){
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_cart_list?uid=' + app.globalData.uid,
      method: 'POST',
      header: header,
      success: function (res) {
        if(res.data.code == 200){
           that.setData({
             cartList: res.data.data.valid,
             cartInvalid: res.data.data.invalid
           })
        }
      }
    })
  },
  //加
  numAddClick: function (event) {
    var index = event.currentTarget.dataset.index;
    this.data.cartList[index].cart_num = +this.data.cartList[index].cart_num + 1;
    var minusStatus = this.data.cartList[index].cart_num <= 1 ? 'disabled' : 'normal';
    this.setData({ cartList: this.data.cartList, minusStatus: minusStatus});
    this.carnum();
    this.countmoney();
    this.addCartNum(this.data.cartList[index].cart_num, this.data.cartList[index].id);
  },
  //减
  numDescClick: function (event) {
    var index = event.currentTarget.dataset.index;
    this.data.cartList[index].cart_num = this.data.cartList[index].cart_num <= 1 ? 1 : +this.data.cartList[index].cart_num - 1;
    var minusStatus = this.data.cartList[index].cart_num <= 1 ? 'disabled' : 'normal';
    this.setData({ cartList: this.data.cartList, minusStatus: minusStatus});
    this.carnum();
    this.countmoney();
    this.addCartNum(this.data.cartList[index].cart_num, this.data.cartList[index].id);
  },
//单选；
  switchSelect:function(e){
    var index = e.currentTarget.dataset.index;
    this.data.cartList[index].checked = !this.data.cartList[index].checked;
    var len = this.data.cartList.length;
    var selectnum = [];
    for(var i = 0; i < len ;i++){
      if (this.data.cartList[i].checked==true){
        selectnum.push(true);
      }
    }
    if (selectnum.length==len){
       this.data.isAllSelect = true;
    }else{
      this.data.isAllSelect = false;
    }
    this.setData({
      cartList: this.data.cartList,
      isAllSelect: this.data.isAllSelect
        });
    this.carnum();
    this.countmoney();
    this.confirmOrder();
  },
  //全选
  allChecked:function(e){
    var selectAllStatus = this.data.isAllSelect;
    selectAllStatus = !selectAllStatus;
    var array = this.data.cartList;
    for (var i = 0; i < array.length;i++){
      array[i].checked = selectAllStatus;
    };
    this.setData({
      cartList:this.data.cartList,
      isAllSelect: selectAllStatus
    })
    this.carnum();
    this.countmoney();
    this.confirmOrder();
  },
  //数量
  carnum(){
     var carnum = 0;
     var array = this.data.cartList;
     for(var i = 0;i<array.length;i++){
       if (array[i].checked==true){
         carnum += parseInt(array[i].cart_num);
       } 
     }
     this.setData({
       cartNum: carnum
     })
  },
  //总共价钱；
  countmoney(){
     var carmoney = 0;
     var array = this.data.cartList;
     for (var i = 0; i < array.length; i++) {
       if (array[i].checked == true) {
         if (array[i].productInfo.attrInfo){
           carmoney += parseFloat(array[i].cart_num * array[i].productInfo.attrInfo.price);
         }else{
           carmoney += parseFloat(array[i].cart_num * array[i].productInfo.price);
         }
       }
     }
     this.setData({
       countmoney: carmoney.toFixed(2)
     })
  },
  confirmOrder:function(){
    var array = this.data.cartList;
    var cartIds = [];
    for (var i = 0; i < array.length; i++) {
      if (array[i].checked == true) {
        cartIds.push(array[i].id);
      }
    }
    if (cartIds.length > 0) {
      this.goConfirm(cartIds);
    }else{
      this.setData({
        cartIdsStr: ''
      })
    }
  },
  goConfirm : function(cartIds){
    if (cartIds.length > 0) {
      this.setData({
        cartIdsStr: cartIds.join(',')
      })
    }
    // console.log(this);
  },
  addCartNum: function (cartNum, cartId){
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    wx.request({
      url: app.globalData.url + '/routine/auth_api/change_cart_num?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        cartNum: cartNum,
        cartId: cartId
      },
      header: header,
      success: function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: '成功',
            icon: 'success',
            duration: 2000
          })
        } else {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          })
        }
      }
    })
  },
  collectAll: function () {
    var array = this.data.cartList;
    var productIds = [];
    var that = this;
    for (var i = 0; i < array.length; i++) {
      if (array[i].checked == true) {
        productIds.push(array[i].product_id);
      }
    }
    if (productIds.length > 0) {
      var header = {
        'content-type': 'application/x-www-form-urlencoded',
      };
      wx.request({
        url: app.globalData.url + '/routine/auth_api/collect_product_all?uid=' + app.globalData.uid,
        method: 'GET',
        data: {
          productId: productIds.join(',')
        },
        header: header,
        success: function (res) {
          if (res.data.code == 200) {
            wx.showToast({
              title: '收藏成功',
              icon: 'success',
              duration: 2000
            })
          } else {
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 2000
            })
          }
        }
      })
    }
    console.log(productIds);
  },
  cartDelAll:function(){
    var array = this.data.cartList;
    var ids = [];
    var that = this;
    for (var i = 0; i < array.length; i++) {
      if (array[i].checked == true) {
        ids.push(array[i].id);
      }
    }
    if (ids.length > 0){
      var header = {
        'content-type': 'application/x-www-form-urlencoded',
      };
      wx.request({
        url: app.globalData.url + '/routine/auth_api/remove_cart?uid=' + app.globalData.uid,
        method: 'GET',
        data: {
          ids: ids.join(',')
        },
        header: header,
        success: function (res) {
          if (res.data.code == 200) {
            wx.showToast({
              title: '删除成功',
              icon: 'success',
              duration: 2000
            })
            for (var i = 0; i < ids.length; i++) {
              for (var j = 0; j < array.length; j++) {
                if (ids[i] == array[j].id) {
                  array.splice(j, 1);
                }
              }
            }
            that.setData({
              cartList: array
            })
          } else {
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 2000
            })
          }
        }
      })
    }
  },
  cartDel:function(e){
    var that = this;
    if (e.currentTarget.dataset.id){
      var header = {
        'content-type': 'application/x-www-form-urlencoded',
      };
      wx.request({
        url: app.globalData.url + '/routine/auth_api/remove_cart?uid=' + app.globalData.uid,
        method: 'GET',
        data: {
          ids: e.currentTarget.dataset.id
        },
        header: header,
        success: function (res) {
           if(res.data.code == 200){
             wx.showToast({
               title: '删除成功',
               icon: 'success',
               duration: 2000
             })
             var cartInvalid = that.data.cartInvalid;
             for (var i = 0; i < cartInvalid.length ; i++){
               if (e.currentTarget.dataset.id == cartInvalid[i].id){
                 cartInvalid.splice(i,1);
                 that.setData({
                   cartInvalid: cartInvalid
                 })
               }
             }
           }else{
             wx.showToast({
               title: res.data.msg,
               icon: 'none',
               duration: 2000
             })
           }
        }
      })
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
 

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.carnum();
    this.countmoney();
    this.getList();
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
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})