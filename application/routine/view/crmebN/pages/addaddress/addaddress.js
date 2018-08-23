var app = getApp();
Page({
  data: {
    _num:1,
    region: ['省','市','区'],
    cartId : '',
    pinkId : '',
    couponId : '',
    id:0,
    userAddress:[]
  },
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    if (options.cartId){
      this.setData({
        cartId: options.cartId,
        pinkId: options.pinkId,
        couponId: options.couponId,
      })
    }
    if (options.id){
      this.setData({
        id: options.id
      })
      this.getUserAddress();
    }
  },
  getUserAddress: function () {//get_user_address
    var that = this;
    
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_user_address?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        addressId : that.data.id
      },
      success: function (res) {
        console.log(res);
        var regionOne = "region.0";
        var regionTwo = "region.1";
        var regionTherr = "region.2";
        that.setData({
          userAddress: res.data.data,
          [regionOne]: res.data.data.province,
          [regionTwo]: res.data.data.city,
          [regionTherr]: res.data.data.district,
          _num: res.data.data.is_default == 1 ? 0 : 1
        })
      }
    })
  },
  bindRegionChange: function (e) {
    this.setData({
      region: e.detail.value
    })
  },
  defaulttap:function(e){
    var num = this.data._num;
    if(num==1){
      this.setData({
        _num: 0
      })
    }else{
      this.setData({
        _num: 1
      })
    }
  },
  
   formSubmit: function (e) {
    var warn = "";
    var that = this;
    var flag = true;
    var cartId = '';
    var name = e.detail.value.name;
    var phone = e.detail.value.phone;
    var area = JSON.stringify(this.data.region);
    var fulladdress = e.detail.value.fulladdress;
    var addressP = {};
    if (name == "") {
      warn = '请输入姓名';
    } else if (!/^1(3|4|5|7|8)\d{9}$/i.test(phone)) {
      warn = '您输入的手机号有误'
    } else if (area == '["省","市","区"]'){
      warn = '请选择地区';
    } else if (fulladdress == "") {
      warn = "请填写具体地址";
    } else{
      flag = false;
    }
    if(flag==true){
      wx.showModal({
        title: '提示',
        content: warn
      })
    }else{
      addressP.province = this.data.region[0];
      addressP.city = this.data.region[1];
      addressP.district = this.data.region[2];
     
      wx.request({
        url: app.globalData.url + '/routine/auth_api/edit_user_address?uid=' + app.globalData.uid,
        method: 'POST',
        data: {
          address: addressP,
          is_default : that.data._num == 0 ? 1 : 0,
          real_name : name,
          post_code : '',
          phone : phone,
          detail : fulladdress,
          id:that.data.id
        },
        success: function (res) {
          if (res.data.code == 200) {
            if (that.data.id) {
              wx.showToast({
                title: '修改成功',
                icon: 'success',
                duration: 1000
              })
            }else{
              wx.showToast({
                title: '添加成功',
                icon: 'success',
                duration: 1000
              })
            }
            setTimeout(function () {
              if (that.data.cartId) {

                var cartId = that.data.cartId;
                var pinkId = that.data.pinkId;
                var couponId = that.data.couponId;
                that.setData({
                  cartId: '',
                  pinkId: '',
                  couponId:'',
                })
                wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
                  url: '/pages/order-confirm/order-confirm?id=' + cartId + '&addressId=' + that.data.id + '&pinkId=' + pinkId + '&couponId=' + couponId
                })
              } else {
                wx.navigateTo({ //跳转至指定页面并关闭其他打开的所有页面（这个最好用在返回至首页的的时候）
                  url: '/pages/address/address'
                })
              }
            },1200)
          }
        }
      })
    }
  }


})