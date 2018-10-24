// pages/news-list/news-list.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    newtext:"查看全文>>",
    _num:'',
    page:0,
    limit:8,
    title:'加载中···',
    hidden:true,
    newsList:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.setBarColor();
    app.setUserInfo();
    this.getNoticeList();
  },
  getNoticeList:function(){
    var that = this;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_notice_list?uid=' + app.globalData.uid,
      method: 'GET',
      data:{
        page: that.data.page,
        limit: that.data.limit,
      },
      success: function (res) {
        that.setData({
          newsList: res.data.data.list
        })
      }
    });
  },
  lookMore:function(e){
    var obj = this.data.newtext;
    var that = this;
    var newsList = that.data.newsList;
    var id = e.currentTarget.dataset.id;
    if (obj =="查看全文>>"){
      newsList.forEach(function (value, index, newsList){
        if (value.id == id && !value.is_see) {
          wx.request({
            url: app.globalData.url + '/routine/auth_api/see_notice?uid=' + app.globalData.uid,
            method: 'GET',
            data: {
              nid: id,
            },
            success: function (res) {
              if (res.data.code == 200){
                var see = "newsList[" + index + "].is_see";
                that.setData({
                  [see]: 1
                })
              }
            }
          });
        }
      })
      that.setData({
        _num: id,
        newtext: "点击收起>>"
      })
    }else{
      that.setData({
        _num: -1,
        newtext: "查看全文>>"
      })
    } 
  },
  onReachBottom: function (p) {
    var that = this;
    that.setData({
      hidden:false,
    })
    var limit = that.data.limit;
    var offset = that.data.page;
    if (!offset) offset = 1;
    var startpage = limit * offset;
    wx.request({
      url: app.globalData.url + '/routine/auth_api/get_notice_list?uid=' + app.globalData.uid,
      data: { limit: limit, page: offset },
      method: 'GET',
      success: function (res) {
        var len = res.data.data.list.length;
        for (var i = 0; i < len; i++) {
          that.data.newsList.push(res.data.data.list[i])
        }
        that.setData({
          page: offset + 1,
          newsList: that.data.newsList
        })
        if (len < limit) {
          that.setData({
            title: "数据已经加载完成",
          });
        }
      },
      fail: function (res) {
        console.log('submit fail');
      },
      complete: function (res) {
        console.log('submit complete');
      }
    })
  },
})