// pages/refund-page/refund-page.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: app.globalData.urlImages,
    index: 0,
    orderId:'',
    dataimg:[],
    hidden:false,
    array: ["请选择", "招商银行实打实打算", "建设银行实打实大苏打", "农业银行实打实大苏打"],
    order:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
     if (options.orderId){
       this.setData({
         orderId: options.orderId
       })
       this.getOrderContent();
       this.getRefundReason();
     } else {
       wx.showToast({
         title: '参数错误',
         icon: 'none',
         duration: 1000
       })
       setTimeout(function () {
         wx.navigateBack({ changed: true });
       }, 1200)
     }
  },
  getRefundReason:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_refund_reason?uid=' + app.globalData.uid,
      method: 'get',
      success: function (res) {
        that.setData({
          array: res.data.data
        });
      },
      fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {
        console.log('submit complete');
      }
    });
  },
  getOrderContent:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_order?uid=' + app.globalData.uid,
      data: { uni: that.data.orderId },
      method: 'get',
      success: function (res) {
        wx.hideLoading();
        that.setData({
          order: res.data.data
        });
      },
      fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {
        console.log('submit complete');
      }
    });
  },
  uploadpic: function (e) {
    var that = this;
    wx.chooseImage({
      count: 1,  //最多可以选择的图片总数  
      sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有  
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有  
      success: function (res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片  
        var tempFilePaths = res.tempFilePaths;
        //启动上传等待中...  
        wx.showLoading({
          title: '图片上传中',
        })
        var len = tempFilePaths.length;
        for (var i = 0; i < len; i++) {
          wx.uploadFile({
            url: app.globalData.url + '/routine/auth_api/upload?uid=' + app.globalData.uid,
            filePath: tempFilePaths[i],
            name: 'refund',
            formData: {
              'filename': 'refund'
            },
            header: {
              "Content-Type": "multipart/form-data"
            },
            success: function (res) {
              wx.hideLoading();
              var data = JSON.parse(res.data);
              if (data.code == 200) {
                that.data.dataimg.push(app.globalData.url+data.data.url);
              }
              that.setData({
                dataimg: that.data.dataimg
              });
              var len2 = that.data.dataimg.length;
              if (len2 >= 3) {
                that.setData({
                  hidden: true
                });
              }
            },
            fail: function (res) {
              wx.showToast({
                title: '上传图片失败',
                icon: 'none',
                duration: 2000
              })
            }
          });
        }
      }
    });
  },
  delImg:function(e){
    var that = this;
    that.data.dataimg.splice(e.target.dataset.index, 1);
    if (that.data.dataimg.length < 3) {
      that.setData({
        dataimg: that.data.dataimg,
        hidden: false
      })
    } else {
      that.setData({
        dataimg: that.data.dataimg
      })
    }
    wx.showToast({
      title: '删除成功',
      icon: 'success',
      duration: 2000
    })
  },
  subRefund:function(e){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_form_id?uid=' + app.globalData.uid,
      method: 'GET',
      data: {
        formId: e.detail.formId
      },
      success: function (res) { }
    })
    if (!that.data.index) {
      wx.showToast({
        title: '请选择退款原因',
        icon: 'none',
        duration: 2000
      })
    }else{
      var header = {
        'content-type': 'application/x-www-form-urlencoded'
      };
      wx.request({
        url: app.globalData.url + '/routine/auth_api/apply_order_refund?uid=' + app.globalData.uid + '&uni=' + that.data.orderId,
        data: {
           text: that.data.array[that.data.index],
           refund_reason_wap_explain: e.detail.value.refund_reason_wap_explain,
           refund_reason_wap_img: that.data.dataimg
        },
        method: 'POST',
        header: header,
        success: function (res) {
          if (res.data.code == 200) {
            wx.showToast({
              title: '申请成功',
              icon: 'success',
              duration: 2000
            })
            setTimeout(function(){
              wx.navigateTo({
                url: '/pages/orders-con/orders-con?order_id=' + that.data.orderId
              })
            },1500)
          }else{
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 2000
            })
          }
        },
        fail: function (res) {
          console.log('submit fail');
        }
      });
    }
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },
  bindPickerChange: function (e) {
    this.setData({
      index: e.detail.value
    })
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
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})