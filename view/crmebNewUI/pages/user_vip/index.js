// pages/member-center/index.js
const app=getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '会员中心',
      'class':'1',
      'color':true
    },
    VipList: [],
    indicatorDots: false,
    circular: true,
    autoplay: false,
    interval: 3000,
    duration: 500,
    swiperIndex: 0,
    growthValue: true,
    task:[],//任务列表
    illustrate:'',//任务说明
    level_id:0,//任务id,
    host_product:[],
  },
  /**
   * 授权回调
  */
  onLoadFun:function(){
    this.setLeveLComplete();
    this.get_host_product();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    setTimeout(function () {
      that.setData({
        loading: true
      })
    }, 500)
  },
  /**
  * 获取我的推荐
 */
  get_host_product: function () {
    var that = this;
    app.baseGet(app.U({ c: 'public_api', a: "get_hot_product", q: { offset: 1, limit: 4 } }), function (res) {
      that.setData({ host_product: res.data });
    });
  },
  /**
   * 会员切换
   * 
  */
  bindchange(e) {
    var index = e.detail.current
    this.setData({swiperIndex: index,level_id: this.data.VipList[index].id || 0});
    this.getTask();
  },
  /**
   * 关闭说明
  */
  growthValue:function(){
    this.setData({growthValue: true})
  },
  /**
   * 打开说明
  */
  opHelp:function(e){
    var index = e.currentTarget.dataset.index;
    this.setData({ growthValue: false, illustrate: this.data.task[index].illustrate});
  },
  /**
   * 设置会员
  */
  setLeveLComplete:function(){
    app.baseGet(app.U({ c: "public_api", a:"set_level_complete"}),function(){
      this.getVipList();
    }.bind(this),null,true);
  },
  /**
   * 获取会员等级
   * 
  */
  getVipList:function(){
    var that=this;
    app.baseGet(app.U({ c: 'public_api', a:'get_level_list'}),function(res){
      that.setData({ 
        VipList: res.data.list, 
        task: res.data.task.task, 
        reach_count: res.data.task.reach_count, 
        level_id:res.data.list[0] ? res.data.list[0].id : 0
      });
    });
  },
  /**
   * 获取任务要求
  */
  getTask:function(){
    var that=this;
    app.baseGet(app.U({ c: 'public_api', a: 'get_task', q: { level_id: that.data.level_id}}),function(res){
      that.setData({ task: res.data.task, reach_count: res.data.reach_count});
    });
  },







})