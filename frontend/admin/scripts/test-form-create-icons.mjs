import assert from 'node:assert/strict';
import * as ElementPlusIcons from '@element-plus/icons-vue';
import { normalizeFormCreateRule } from '../src/utils/formCreateIcon.js';

const original = {
  type: 'frame',
  field: 'wechat_qrcode',
  title: '关注二维码',
  props: { icon: 'el-icon-picture-outline', handleIcon: 'el-icon-user' },
  children: [{ type: 'frame', props: { icon: 'md-add' } }],
  control: [{ value: 1, rule: [{ type: 'frame', props: { icon: 'el-icon-picture-outline' } }] }],
};

const normalized = normalizeFormCreateRule(original);
assert.equal(normalized.props.icon, 'Picture');
assert.equal(normalized.props.handleIcon, 'User');
assert.equal(normalized.children[0].props.icon, 'Plus');
assert.equal(normalized.control[0].rule[0].props.icon, 'Picture');
for (const icon of ['Picture', 'User', 'UserFilled', 'Plus']) {
  assert.ok(ElementPlusIcons[icon], `${icon} must be an Element Plus icon component`);
}
assert.equal(original.props.icon, 'el-icon-picture-outline');
assert.equal(original.control[0].rule[0].props.icon, 'el-icon-picture-outline');
assert.equal(normalizeFormCreateRule({ props: { icon: 'UnknownIcon' } }).props.icon, 'UnknownIcon');

console.log('form-create icon compatibility passed');
