var app = getApp();
Component({
  properties: {
    imgUrls:{
      type:Object,
      value:[]
    }
  },
  data: {
    indicatorDots: false,
    circular: true,
    autoplay: false,
    interval: 3000,
    duration: 500,
    currents: "1"
  },
  attached:function(){
  },
  methods: {
    change: function (e) {
      this.setData({
        currents: e.detail.current + 1
      })
    }
  }
})