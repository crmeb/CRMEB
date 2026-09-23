// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 图片按钮（wangeditor v5 自定义 IButtonMenu）
// 点击后通过事件总线通知编辑器组件打开项目统一的图片选择弹窗
//（素材库多选，替代 v5 内置 uploadImage 的 base64 直插），对齐 VideoMenu 写法
import bus from '@/utils/bus';

const IMAGE_SVG =
  '<svg viewBox="0 0 1024 1024"><path d="M864 128H160c-17.7 0-32 14.3-32 32v704c0 17.7 14.3 32 32 32h704c17.7 0 32-14.3 32-32V160c0-17.7-14.3-32-32-32z m-40 696H200V296h624v528zM304 448c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64z m-40 280h496l-152-280-124 176-84-112-136 216z"></path></svg>';

export default class ImageMenu {
  constructor() {
    this.title = '图片';
    this.iconSvg = IMAGE_SVG;
    this.tag = 'button';
  }
  exec(editor) {
    bus.emit('UploadImg', editor);
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
