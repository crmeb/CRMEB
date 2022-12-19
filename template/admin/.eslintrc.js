module.exports = {
  root: true,
  parser: 'vue-eslint-parser',
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module',
    ecmaFeatures: {
      jsx: true,
    },
  },

  extends: ['prettier', 'plugin:prettier/recommended'],

  rules: {
    // override/add rules settings here, such as:
  },
};
