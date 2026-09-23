// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 视频按钮（wangeditor v5 自定义 IButtonMenu）
// 点击后通过事件总线通知编辑器组件打开视频选择弹窗（对齐 v4 的 BtnMenu 写法）
import bus from '@/utils/bus';

// wangeditor v5 内置 video-module 同款图标
const VIDEO_SVG =
  '<svg viewBox="0 0 1024 1024"><path d="M981.184 160.096C837.568 139.456 678.848 128 512 128S186.432 139.456 42.816 160.096C15.296 267.808 0 386.848 0 512s15.264 244.16 42.816 351.904C186.464 884.544 345.152 896 512 896s325.568-11.456 469.184-32.096C1008.704 756.192 1024 637.152 1024 512s-15.264-244.16-42.816-351.904zM384 704V320l320 192-320 192z"></path></svg>';

export default class VideoMenu {
  constructor() {
    // v5 按钮：title 用于鼠标悬停提示（iconSvg 存在时展示图标，否则展示 title 文本）
    this.title = '视频';
    this.iconSvg = VIDEO_SVG;
    this.tag = 'button';
  }
  // 菜单点击事件（对应 v4 的 clickHandler）
  exec(editor) {
    bus.emit('Video', editor);
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
