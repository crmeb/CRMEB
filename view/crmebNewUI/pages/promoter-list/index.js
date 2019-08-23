// pages/promoter-list/index.js
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '推广人列表',
      'color': true,
      'class': '0'
    },
    total:0,
    totalLevel:0,
    teamCount: 0,
    page: 0,
    limit: 20,
    keyword:'',
    sort:'',
    grade:0,
    status: false,
    recordList:[],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.userSpreadNewList();
  },
  setSort: function (e) {
    var that = this;
    that.setData({
      sort: e.currentTarget.dataset.sort,
      page: 0,
      limit: 20,
      status: false,
      recordList: [],
    });
    that.userSpreadNewList();
  },
  setKeyword: function (e) {
    this.setData({ keyword: e.detail.value });
  },
  setRecordList: function () {
    this.setData({
      page: 0,
      limit: 20,
      status: false,
      recordList: [],
    });
    this.userSpreadNewList();
  },
  setType:function(e){
    if (this.data.grade != e.currentTarget.dataset.grade) {
      this.setData({
        grade: e.currentTarget.dataset.grade,
        page: 0,
        limit: 20,
        keyword: '',
        sort: '',
        status: false,
        recordList: [],
      });
      this.userSpreadNewList();
    }
  },
  userSpreadNewList: function () {
    var that = this;
    var page = that.data.page;
    var limit = that.data.limit;
    var status = that.data.status;
    var keyword = that.data.keyword;
    var sort = that.data.sort;
    var grade = that.data.grade;
    var recordList = that.data.recordList;
    var recordListNew = [];
    if (status == true) return;
    app.baseGet(app.U({ c: 'user_api', a: 'user_spread_new_list', q: { 
      page: page, 
      limit: limit,
      keyword: keyword,
      grade: grade,
      sort: sort,
      } }), function (res) {
      var len = res.data.list.length;
      var recordListData = res.data.list;
      recordListNew = recordList.concat(recordListData);
      that.setData({
        total: res.data.total,
        totalLevel: res.data.totalLevel,
        teamCount: Number(res.data.total) + Number(res.data.totalLevel),
        status: limit > len, 
        page: limit + page, 
        recordList: recordListNew 
      });
    });
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
    this.userSpreadNewList();
  }
})