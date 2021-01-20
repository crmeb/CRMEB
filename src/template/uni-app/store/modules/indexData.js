export default {
	namespaced: true,
	state: {
		// 搜索关键字
		indexDatas: {}
	},
	getters: {},
	mutations: {
		setIndexData(state, data) {
			state.indexDatas = data;
		}
	}
}
