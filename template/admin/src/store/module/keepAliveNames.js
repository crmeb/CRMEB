const keepAliveNamesModule = {
  namespaced: true,
  state: {
    keepAliveNames: [],
  },
  mutations: {
    // 设置路由缓存（name字段）
    getCacheKeepAlive(state, data) {
      state.keepAliveNames = data;
    },
  },
  actions: {
    // 设置路由缓存（name字段）
    async setCacheKeepAlive({ commit }, data) {
      commit('getCacheKeepAlive', data);
    },
  },
};

export default keepAliveNamesModule;
