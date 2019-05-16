var app = getApp();
Component({
  properties: {
    coupon: {
      type: Object,
      value:{
        list:[],
        statusTile:''
      },
    },
    //打开状态 0=领取优惠券,1=使用优惠券
    openType:{
      type:Number,
      value:0,
    }
  },
  data: {
  },
  attached: function () {
  },
  methods: {
    close: function () {
      this.triggerEvent('ChangCouponsClone');
    },
    getCouponUser:function(e){
      var that = this;
      var id = e.currentTarget.dataset.id;
      var index = e.currentTarget.dataset.index;
      var list = that.data.coupon.list;
      if (list[index].is_use == true && this.data.openType==0) return true;
      switch (this.data.openType){
        case 0:
          //领取优惠券
          app.basePost(app.U({ c: 'coupons_api', a: 'user_get_coupon' }), { couponId:id},function(res){
            list[index].is_use=true;
            that.setData({
              ['coupon.list']: list
            });
            app.Tips({ title: '领取成功' });
            that.triggerEvent('ChangCoupons',list[index]);
          });
        break;
        case 1:
          that.triggerEvent('ChangCoupons',index);
        break;
      }
    },
  }
})