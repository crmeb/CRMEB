// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

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
    pageName: '',
    //当删除数据并提交时存储默认数据(可视化)
    uploadListDataswiperBg: {},
    uploadListDatamenus: {},
    uploadListDataactivity: {},
    uploadListDatarecommend: {},
    uploadListDataadsRecommend: {},
    txtListData: {},
  },
  mutations: {
    //当删除数据并提交时存储默认数据(可视化)
    uploadListswiperBg(state, data) {
      state.uploadListDataswiperBg = data;
    },
    uploadListmenus(state, data) {
      state.uploadListDatamenus = data;
    },
    uploadListrecommend(state, data) {
      state.uploadListDatarecommend = data;
    },
    uploadListactivity(state, data) {
      state.uploadListDataactivity = data;
    },
    uploadListadsRecommend(state, data) {
      state.uploadListDataadsRecommend = data;
    },
    txtList(state, data) {
      state.txtListData = data;
    },
    //
    setPageName(state, id) {
      state.pageName = id;
    },
    userInfo(state, userInfo) {
      state.userInfo = userInfo;
    },
    uniqueAuth(state, uniqueAuth) {
      state.uniqueAuth = uniqueAuth;
    },
    name(state, name) {
      state.name = name;
    },
    avatar(state, avatar) {
      state.avatar = avatar;
    },
    access(state, access) {
      state.access = access;
    },
    logo(state, logo) {
      state.logo = logo;
    },
    logoSmall(state, logoSmall) {
      state.logoSmall = logoSmall;
    },
    version(state, version) {
      state.version = version;
    },
    newOrderAudioLink(state, newOrderAudioLink) {
      state.newOrderAudioLink = newOrderAudioLink;
    },
  },
  actions: {
    getMenusNavList({ commit }) {
      return new Promise((resolve, reject) => {
        menusApi()
          .then(async (res) => {
            resolve(res);
            commit('getmenusNav', res.data.menus);
          })
          .catch((res) => {
            reject(res);
          });
      });
    },
  },
};
