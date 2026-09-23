import directive from './directives';

const importDirective = (app) => {
  /**
   * 拖拽指令 v-draggable="options"
   * options = {
   *  trigger: /这里传入作为拖拽触发器的CSS选择器/,
   *  body:    /这里传入需要移动容器的CSS选择器/,
   *  recover: /拖动结束之后是否恢复到原来的位置/
   * }
   */
  app.directive('draggable', directive.draggable);
  /**
   * clipboard指令 v-clipboard="options"
   * options = {
   *  value:    /在输入框中使用v-model绑定的值/,
   *  success:  /复制成功后的回调/,
   *  error:    /复制失败后的回调/
   * }
   */
  app.directive('clipboard', directive.clipboard);
  /**
   * v-auth="['string-string']"
   * */
  app.directive('auth', directive.auth);

  app.directive('permission', directive.permission);
  app.directive('dbClick', directive.dbClick);
};

export default importDirective;
