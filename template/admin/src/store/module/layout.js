export default {
  namespaced: true,
  state: {
    menuCollapse: false,
  },
  getters: {},
  mutations: {
    /**
     * @description 设置侧边栏展开关闭
     * @param {Object} state vuex state
     * @param {Array} status status
     */
    changeCol(state, status) {
      state.menuCollapse = status;
    },
  },
};
