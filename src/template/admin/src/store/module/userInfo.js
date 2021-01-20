export default {
  namespaced: true,
  state: {
    userInfo: null,
    uniqueAuth: [],
    name: '',
    avatar: '',
    access: '',
    logo: '',
    logoSmall: '',
    version: '',
    newOrderAudioLink: '',
    pageName: ''
  },
  mutations: {
    setPageName(state,id){
      console.log('模板二');
      console.log(id);
      state.pageName = id
    },
    userInfo (state, userInfo) {
      state.userInfo = userInfo
      console.log(userInfo)
    },
    uniqueAuth (state, uniqueAuth) {
      state.uniqueAuth = uniqueAuth
    },
    name (state, name) {
      state.name = name
    },
    avatar (state, avatar) {
      state.avatar = avatar
    },
    access (state, access) {
      state.access = access
    },
    logo (state, logo) {
      state.logo = logo
    },
    logoSmall (state, logoSmall) {
      state.logoSmall = logoSmall
    },
    version (state, version) {
      state.version = version
    },
    newOrderAudioLink (state, newOrderAudioLink) {
      state.newOrderAudioLink = newOrderAudioLink
    }
  },
  actions: {
    getMenusNavList ({ commit }) {
      return new Promise((resolve, reject) => {
        menusApi().then(async res => {
          resolve(res)
          commit('getmenusNav', res.data.menus)
        }).catch(res => {
          reject(res)
        })
      })
    }
  }
}
