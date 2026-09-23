// form-create 的 frame 将 icon 作为组件名解析，后端存量规则仍使用 element-ui 图标类名。
const FORM_CREATE_ICON_MAP = {
  'el-icon-picture-outline': 'Picture',
  'el-icon-user': 'User',
  'el-icon-user-solid': 'UserFilled',
  'md-add': 'Plus',
};

export function normalizeFormCreateIcon(icon) {
  return typeof icon === 'string' ? FORM_CREATE_ICON_MAP[icon] || icon : icon;
}

export function normalizeFormCreateRule(rule) {
  if (!rule || typeof rule !== 'object') return rule;

  const normalizedRule = { ...rule };
  if (typeof normalizedRule.icon === 'string') {
    normalizedRule.icon = normalizeFormCreateIcon(normalizedRule.icon);
  }

  ['props', 'info', 'title'].forEach((key) => {
    const config = normalizedRule[key];
    if (!config || typeof config !== 'object' || Array.isArray(config)) return;
    normalizedRule[key] = { ...config };
    ['icon', 'handleIcon'].forEach((iconKey) => {
      if (iconKey in normalizedRule[key]) {
        normalizedRule[key][iconKey] = normalizeFormCreateIcon(normalizedRule[key][iconKey]);
      }
    });
  });

  if (Array.isArray(normalizedRule.children)) {
    normalizedRule.children = normalizedRule.children.map(normalizeFormCreateRule);
  }
  if (Array.isArray(normalizedRule.control)) {
    normalizedRule.control = normalizedRule.control.map((control) =>
      Array.isArray(control?.rule) ? { ...control, rule: control.rule.map(normalizeFormCreateRule) } : control,
    );
  }

  return normalizedRule;
}
