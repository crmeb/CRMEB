import E from 'wangeditor'; // npm 安装
// const E = window.wangEditor // CDN 引入的方式
import util from '../../utils/bus';
// 获取必要的变量，这些在下文中都会用到
const { $, BtnMenu, DropListMenu, PanelMenu, DropList, Panel, Tooltip } = E;
var _this = null;
export default class HtmlMenu extends BtnMenu {
  constructor(editor) {
    _this = editor;
    // data-title属性表示当鼠标悬停在该按钮上时提示该按钮的功能简述
    const $elem = E.$(
      `<div class="w-e-menu" data-title="html">
                <div>HTML</div>
            </div>`,
    );
    super($elem, editor);
  }
  // 菜单点击事件
  clickHandler() {
    // getHtmlint()
    util.$emit('Html');
  }
  tryChangeActive() {
    this.active();
  }
}
