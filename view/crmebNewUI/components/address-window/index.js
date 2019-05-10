var app = getApp();
Component({
  properties: {
    //跳转url链接
    pagesUrl:{
      type:String,
      value:'',
    },
    address:{
      type: Object,
      value:{
        address:true,
        addressId:0,
      }
    },
    isLog:{
      type:Boolean,
      value:false,
    },
  },
  data: {
    active: 0,
    //地址列表
    addressList:[],
  },
  attached: function () {
    
  },
  methods: {
    tapAddress: function (e) {
      this.setData({ active: e.currentTarget.dataset.id });
      this.triggerEvent('OnChangeAddress', e.currentTarget.dataset.addressid);
    },
    close: function () {
      this.setData({ 'address.address': false });
      this.triggerEvent('changeTextareaStatus');
    },
    goAddressPages:function(){
      this.setData({ 'address.address': false });
      this.triggerEvent('changeTextareaStatus');
      wx.navigateTo({url: this.data.pagesUrl});
    },
    getAddressList:function(){
      var that=this;
      app.baseGet(app.U({ c: "user_api", a:'user_address_list'}),function(res){
        var addressList=res.data;
        //处理默认选中项
        for (var i = 0, leng = addressList.length; i < leng;i++){
          if (addressList[i].id == that.data.address.addressId) that.setData({ active:i});
        }
        that.setData({ addressList: addressList});
      });
    }
  }
})