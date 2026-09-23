// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

// vuedraggable 稳定 item-key（参照 single_admin 主题迁移修复）
// 不要用 num 等业务字段作 item-key：添加/移动组件后 forEach 会全量改写 num，
// 同一 element 的 key 随之变化，vuedraggable 内部 key↔element 映射与 Vue 的
// keyed diff 错乱，patch 卸载阶段会抛
// "Cannot read properties of null (reading 'exposed'/'type')" 中断渲染。
// WeakMap 按 element 对象发一次性自增 key，元素本身不变则 key 恒定。
const draggableKeyMap = new WeakMap();
let draggableKeySeed = 0;

export function getDraggableItemKey(item) {
  if (!item || (typeof item !== 'object' && typeof item !== 'function')) {
    return `draggable-${String(item)}`;
  }
  if (!draggableKeyMap.has(item)) {
    draggableKeySeed += 1;
    draggableKeyMap.set(item, `draggable-${draggableKeySeed}`);
  }
  return draggableKeyMap.get(item);
}
