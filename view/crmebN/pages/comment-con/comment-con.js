// pages/comment-con/comment-con.js
var app = getApp();
var wxh = require('../../utils/wxh.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    xinghidden:true,
    xinghidden2:true,
    xinghidden3: true,
    url: '',
    hidden:false,
    unique:'',
    uni:'',
    dataimg:[]
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (e) {
    app.setBarColor();
    app.setUserInfo();
    var header = {
      'content-type': 'application/x-www-form-urlencoded'
    };
    var that = this;
    if (e.unique) {
      var unique = e.unique;
      that.setData({
        unique: unique,
      });
    }
    if (e.uni){
      that.setData({
        uni: e.uni
      });
    }
    wx.showLoading({ title: "正在加载中……" });
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_order_product?uid=' + app.globalData.uid,
      data: { unique: unique},
      method: 'get',
      header: header,
      success: function (res) {
        wx.hideLoading();
        that.setData({
          ordercon: res.data.data
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
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },
  tapxing:function(e){
    var index = e.target.dataset.index;
    this.setData({
      xinghidden: false,
      xing: index
    })
  },
  tapxing2: function (e) {
    var index = e.target.dataset.index;
    this.setData({
      xinghidden2: false,
      xing2: index
    })
  },
  tapxing3: function (e) {
    var index = e.target.dataset.index;
    this.setData({
      xinghidden3: false,
      xing3: index
    })
  },
  delImages:function(e){
    var that = this;
    var dataimg = that.data.dataimg;
    var index = e.currentTarget.dataset.id;
    dataimg.splice(index,1);
    that.setData({
      dataimg: dataimg
    })
    
  },
  uploadpic:function(e){
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
            name: 'pics',
            formData: {
              'filename': 'pics'
            },
            header: {
              "Content-Type": "multipart/form-data"
            },
            success: function (res) {
              wx.hideLoading();
              if(res.statusCode == 403){
                   wx.showToast({
                     title: res.data,
                     icon: 'none',
                     duration: 1500,
                   })
              } else {
                var data = JSON.parse(res.data);
                data.data.url = app.globalData.url + data.data.url;
                that.data.dataimg.push(data);
                that.setData({
                  dataimg: that.data.dataimg
                });
                var len2 = that.data.dataimg.length;
                if (len2 >= 8) {
                  that.setData({
                    hidden: true
                  });
                }
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
  formSubmit:function(e){
    var header = {
      'content-type': 'application/x-www-form-urlencoded'
    };
    var that = this;
    var unique = that.data.unique;
    var comment = e.detail.value.comment;
    var product_score = that.data.xing;
    var service_score = that.data.xing2;
    var pics = []; 
    var dataimg = that.data.dataimg;
    for (var i = 0; i < dataimg.length;i++){
      pics.push(that.data.url+dataimg[i].data.url)
    };
    if (comment==""){
      wx.showToast({
        title: '请填写你对宝贝的心得！',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
    wx.showLoading({ title: "正在发布评论……" });
    wx.request({
      url: app.globalData.url + '/routine/auth_api/user_comment_product?uid=' + app.globalData.uid+'&unique=' + unique,
      data: {comment: comment, product_score: product_score, service_score: service_score, pics: pics},
      method: 'post',
      header: header,
      success: function (res) {
        wx.hideLoading();
        if (res.data.code==200){
          wx.showToast({
            title: '评价成功',
            icon: 'success',
            duration: 1000
          })
          setTimeout(function(){
             wx.navigateTo({
               url: '/pages/orders-con/orders-con?order_id='+that.data.uni,
             })
          },1200)
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
      },
      complete: function (res) {
        console.log('submit complete');
      }
    });
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