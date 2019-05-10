var app = getApp();
Component({
  properties: {
    sharePacket:{
      type:Object,
      value:{
        isState: true,
        priceName:'',
      }
    }
  },
  data: {

  },
  attached: function () {
  },
  methods: {
    closeShare:function(){
      this.setData({
        "sharePacket.isState": true
      })
    },
    goShare:function(){
      this.triggerEvent('listenerActionSheet');
    },
  }
})