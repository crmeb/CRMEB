// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// v-lazy 图片懒加载兼容指令（替代 vue-lazyload，基于 IntersectionObserver）
const lazyDirective = {
  mounted(el, binding) {
    el.__lazySrc = binding.value;
    el.__lazyIo = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            el.src = el.__lazySrc;
            el.__lazyIo && el.__lazyIo.disconnect();
            el.__lazyIo = null;
          }
        });
      },
      { rootMargin: '300px' },
    );
    el.__lazyIo.observe(el);
  },
  updated(el, binding) {
    if (binding.value !== binding.oldValue) {
      el.__lazySrc = binding.value;
      if (el.src) el.src = binding.value;
    }
  },
  unmounted(el) {
    el.__lazyIo && el.__lazyIo.disconnect();
    el.__lazyIo = null;
  },
};

export default lazyDirective;
