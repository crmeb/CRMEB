const tagsViewRoutesModule = {
  namespaced: true,
  state: {
    tagsViewRoutes: [],
  },
  mutations: {
    // 设置 TagsView 路由
    getTagsViewRoutes(state, data) {
      state.tagsViewRoutes = data;
    },
  },
  actions: {
    // 设置 TagsView 路由
    async setTagsViewRoutes({ commit }, data) {
      commit('getTagsViewRoutes', data);
    },
  },
};

export default tagsViewRoutesModule;
