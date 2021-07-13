// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import Clipboard from 'clipboard'
export default {
  bind: (el, binding) => {
    const clipboard = new Clipboard(el, {
      text: () => binding.value.value
    });
    el.__success_callback__ = binding.value.success;
    el.__error_callback__ = binding.value.error;
    clipboard.on('success', e => {
      const callback = el.__success_callback__;
      callback && callback(e)
    });
    clipboard.on('error', e => {
      const callback = el.__error_callback__;
      callback && callback(e)
    });
    el.__clipboard__ = clipboard
  },
  update: (el, binding) => {
    el.__clipboard__.text = () => binding.value.value;
    el.__success_callback__ = binding.value.success;
    el.__error_callback__ = binding.value.error
  },
  unbind: (el, binding) => {
    delete el.__success_callback__;
    delete el.__error_callback__;
    el.__clipboard__.destroy();
    delete el.__clipboard__
  }
}
