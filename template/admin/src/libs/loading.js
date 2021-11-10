// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

const events = [];

const $scroll = function(dom, fn) {
  events.push({ dom, fn });
  fn._index = events.length - 1;
};

$scroll.remove = function(fn) {
  fn._index && events.splice(fn._index, 1);
};

//上拉加载；
const Scroll = {
  addHandler: function(element, type, handler) {
    if (element.addEventListener)
      element.addEventListener(type, handler, false);
    else if (element.attachEvent) element.attachEvent("on" + type, handler);
    else element["on" + type] = handler;
  },
  listenTouchDirection: function() {
    this.addHandler(window, "scroll", function() {
      const wh = window.innerHeight,
        st = window.scrollY;
      events
        .filter(e => e.dom.scrollHeight && e.dom.scrollHeight > 0)
        .forEach(e => {
          var dh = e.dom.scrollHeight;
          var s = Math.ceil((st / (dh - wh)) * 100);
          if (s > 85) e.fn();
        });
    });
  }
};

Scroll.listenTouchDirection();

export default $scroll;
export { Scroll };
