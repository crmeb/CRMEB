// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * AI 写作菜单（wangeditor v5 自定义 IButtonMenu）
 *
 * 点击后通过事件总线通知编辑器组件打开 AI 写作弹窗。
 */
import bus from '@/utils/bus';

export default class AiMenu {
  constructor() {
    // v5 按钮无 iconSvg 时直接以 title 文本渲染，保持 v4 的「AI」文字按钮样式
    this.title = 'AI';
    this.tag = 'button';
  }
  // 菜单点击事件（对应 v4 的 clickHandler）
  exec(editor) {
    bus.emit('AiWrite', editor);
  }
  getValue() {
    return '';
  }
  isActive() {
    return false;
  }
  isDisabled() {
    return false;
  }
}
