# Vuex → Pinia Store 映射文档（v8.0.vue3）

全部 22 个 Vuex 模块（`src/store/module/` 21 个 + `src/pages/plugin/kefu/store.js`）已转换为 Pinia setup store。
`src/store/index.js` 现仅导出挂载了 `pinia-plugin-persistedstate` 的 pinia 实例，**不再 import 任何模块**（避免循环依赖）。

## 通用机械替换规则（页面迁移速查）

| Vuex 写法 | Pinia 写法 |
| --- | --- |
| `this.$store.state.menus.childMenuList` | `useMenusStore().childMenuList`（组件内建议 `storeToRefs(useMenusStore())` 解构） |
| `this.$store.commit('menus/getmenusNav', x)` | `useMenusStore().getmenusNav(x)` |
| `this.$store.dispatch('order/getOrderTabs', x)` | `useOrderStore().getOrderTabs(x)`（返回原 Promise） |
| `this.$store.getters['user/messageUnreadCount']` | `useUserStore().messageUnreadCount` |
| `...mapMutations('media', ['setDevice'])` | `const mediaStore = useMediaStore()` 后直接 `mediaStore.setDevice('Mobile')` |
| `...mapState('themeConfig', [...])` | `storeToRefs(useThemeConfigStore())` |
| 非命名空间模块（user/app）：`commit('setUserId', x)` / `getters['errorCount']` | `useUserStore().setUserId(x)` / `useAppStore().errorCount`（函数名不变，仅去掉根级调用形式） |
| store 内互访：`rootState.user` | `useUserStore()`（在函数/computed 内部按需调用） |

- store id 与原 Vuex 模块名完全一致（`'user'`、`'mobildConfig'`（历史拼写，保持）…）。
- 持久化：原 vuex-persist 以单一 key `vuex` 持久化 16 个模块；现改用 `pinia-plugin-persistedstate`，**每个 store 独立 key（默认为 store id）、localStorage**。旧 `vuex` 缓存不会迁移，首次访问需重新登录/初始化，属预期。
- 所有 mutation 转为普通函数、action 转为普通/异步函数，**默认 `this.$store.dispatch` 的返回值语义保持**（原来返回 Promise 的仍返回 Promise）。
- 原 `state.xxx = ...` 全部改为 `xxx.value = ...`，函数内逻辑逐行保留。

## 重命名汇总（页面替换时必看）

| store | 原 mutation/getter 名 | 新函数名 | 原因 |
| --- | --- | --- | --- |
| menus | `childMenuList` | `setChildMenuList` | 与 state 字段 `childMenuList` 同名冲突（layout 组件 6 处调用） |
| userInfo | `userInfo` | `setUserInfo` | 与 state 字段同名冲突 |
| userInfo | `uniqueAuth` | `setUniqueAuth` | 同名冲突 |
| userInfo | `name` | `setName` | 同名冲突 |
| userInfo | `avatar` | `setAvatar` | 同名冲突 |
| userInfo | `access` | `setAccess` | 同名冲突 |
| userInfo | `logo` | `setLogo` | 同名冲突 |
| userInfo | `logoSmall` | `setLogoSmall` | 同名冲突 |
| userInfo | `version` | `setVersion` | 同名冲突 |
| userInfo | `newOrderAudioLink` | `setNewOrderAudioLink` | 同名冲突 |
| upgrade | getter `toggleStatus` | （删除，直接用 `store.toggleStatus`） | 恒等 getter，与 state 字段同名冲突，值完全一致 |

其余全部 mutation/action/getter **名字保持不变**。部分函数的形参名有调整（如 `setAccess(val)`、`TOGGLE_STATUS(status)`），仅因形参遮蔽同名 ref，调用方按位置传参不受影响。

## 行为差异备注（均为原有死代码/笔误的显式化）

1. **menu 模块 `filterSider`/`filterHeader`（computed）**：原引用 `rootState.user.info` / `rootState.admin.user.info`（`admin` 模块不存在，历史笔误，实际意图为 user 模块）。已统一改为 `useUserStore().info`。注意 user store 本身没有 `info` 字段，这两个 getter 与原版一致属死代码（全库无引用），一旦被求值会抛 TypeError（与原 Vuex 行为相同）。
2. **`userInfo/getMenusNavList` action**：原文件未 import `menusApi`（且 commit 的 `getmenusNav` mutation 在该模块不存在），全库无 dispatch 调用。现补上 import 使其可用，并移除指向不存在 mutation 的 commit。
3. **`listenFullscreen`/`toggleFullscreen`（userLevel、order、integralOrder）**：原内部 `commit('setFullscreen', x)` 指向的 mutation 从未存在（Vuex 下仅产生 unknown mutation 警告、实际 no-op），已移除该调用，screenfull 逻辑保留。
4. **mobildConfig 模块级模板**：模块顶部默认模板变量 `bottomMenu` 改名 `defaultBottomMenu`（避免与 state 字段 `bottomMenu` 遮蔽），store 对外 API 不变。
5. **app 模块**：私有辅助函数 `closePage` 从模块顶层移入 setup 内部（原本就不是 mutation/action，未导出）。
6. **`user/setAccess`、`user/setUserId` 等**：user 模块原为非命名空间，页面里可能是 `commit('setAccess', x)`（无前缀），替换为 `useUserStore().setAccess(x)`。

## 各 Store 明细

### 1. useUserStore — `src/store/module/user.js`（id: `user`，persist: true；原模块非命名空间）

- state：`userName`、`userId`、`avatarImgPath`、`access`、`hasGetInfo`、`unreadCount`、`messageUnreadList`、`messageReadedList`、`messageTrashList`、`messageContentStore`
- getter→computed：`messageUnreadCount`、`messageReadedCount`、`messageTrashCount`
- 函数（原 mutation）：`setAvatar`、`setUserId`、`setUserName`、`setAccess`、`setHasGetInfo`、`setMessageCount`、`setMessageUnreadList`、`setMessageReadedList`、`setMessageTrashList`、`updateMessageContentStore`、`moveMsg`
- 函数（原 action）：`getUnreadMessageCount`（空实现，原注释代码已丢弃）

### 2. useAppStore — `src/store/module/app.js`（id: `app`，persist: true；原模块非命名空间）

- state：`breadCrumbList`、`tagNavList`、`homeRoute`、`local`、`errorList`、`adminTitle`、`hasReadErrorPage`
- getter→computed：`errorCount`
- 函数（原 mutation）：`setBreadCrumb`、`setAdminTitle`、`setHomeRoute`、`setTagNavList`、`closeTag`、`addTag`、`setLocal`、`addError`、`setHasReadErrorLoggerStatus`、`clearAll`
- 函数（原 action）：`addErrorLog`（内部 `rootState.user` → `useUserStore()`）

### 3. useMenusStore — `src/store/module/menus.js`（id: `menus`，persist: true）

- state：`menusName`（初值仍从 localStorage `menuList` 读取）、`openMenus`、`childMenuList`、`oneLvMenus`、`oneLvRoutes`
- 函数（原 mutation）：`getmenusNav`、`setopenMenus`、`setOneLvMenus`、`setOneLvRoute`、`setChildMenuList`（原名 `childMenuList`）
- 函数（原 action）：`getMenusNavList`

### 4. useMenuStore — `src/store/module/menu.js`（id: `menu`，persist: true）

- state：`header`、`oneMenuName`、`sider`、`headerName`、`activePath`、`openNames`
- getter→computed：`filterSider`、`filterHeader`、`currentHeader`、`hideSider`（见行为差异备注 1）
- 函数（原 mutation）：`setSider`、`setOpenMenuName`、`setHeader`、`setHeaderName`、`setActivePath`、`setOpenNames`

### 5. useUserInfoStore — `src/store/module/userInfo.js`（id: `userInfo`，persist: true）

- state：`userInfo`、`uniqueAuth`、`name`、`avatar`、`access`、`logo`、`logoSmall`、`version`、`newOrderAudioLink`、`pageName`、`uploadListDataswiperBg`、`uploadListDatamenus`、`uploadListDataactivity`、`uploadListDatarecommend`、`uploadListDataadsRecommend`、`txtListData`
- 函数（原 mutation，名字不变）：`uploadListswiperBg`、`uploadListmenus`、`uploadListrecommend`、`uploadListactivity`、`uploadListadsRecommend`、`txtList`、`setPageName`、`userRealName`、`userRealHeadPic`
- 函数（原 mutation，已改名）：`setUserInfo`（原 `userInfo`）、`setUniqueAuth`（原 `uniqueAuth`）、`setName`（原 `name`）、`setAvatar`（原 `avatar`）、`setAccess`（原 `access`）、`setLogo`（原 `logo`）、`setLogoSmall`（原 `logoSmall`）、`setVersion`（原 `version`）、`setNewOrderAudioLink`（原 `newOrderAudioLink`）
- 函数（原 action）：`getMenusNavList`（死代码，见备注 2）

### 6. useUserInfosStore — `src/store/module/userInfos.js`（id: `userInfos`，**无 persist**）

- state：`userInfos`
- 函数（原 mutation）：`getUserInfos`
- 函数（原 action）：`setUserInfos`

### 7. useUserLevelStore — `src/store/module/userLevel.js`（id: `userLevel`，persist: true）

- state：`taskId`、`levelId`、`categoryId`
- 函数（原 mutation）：`getTaskId`、`getlevelId`、`getCategoryId`
- 函数（原 action）：`listenFullscreen`、`toggleFullscreen`（见备注 3）

### 8. useOrderStore — `src/store/module/order.js`（id: `order`，persist: true）

- state：`orderStatus`、`orderTime`、`orderNum`、`orderType`、`fieldKey`、`orderChartType`、`isDels`、`delIdList`、`iconsaaaa`、`orderPayType`、`real_name`
- 函数（原 mutation）：`getOrderStatus`、`getOrderType`、`getOrderTime`、`getOrderNum`、`getfieldKey`、`setOrderKeyword`、`onChangeTabs`、`onChangeChart`、`getIsDel`、`getisDelIdListl`、`resetSearch`
- 函数（原 action）：`getOrderTabs`、`listenFullscreen`、`toggleFullscreen`

### 9. useMediaStore — `src/store/module/media.js`（id: `media`，persist: true）

- state：`isMobile`、`isTablet`、`isDesktop`、`isFullscreen`
- 函数（原 mutation）：`setDevice(type)`（type ∈ Mobile/Tablet/Desktop）

### 10. useGoodSelectStore — `src/diy/store/legacy/goodSelect.js`（id: `goodSelect`，**无 persist**）

- state：`periphery`、`activeName`、`defaultConfig`、`component`（DIY 默认配置大对象，内容与原版逐字一致）
- 函数（原 mutation）：`setConfig`、`upDataGoodList`、`updataConfig`

### 11. useMorenStore — `src/diy/store/legacy/moren.js`（id: `moren`，**无 persist**）

- state：`activeName`、`defaultConfig`、`component`（内容与原版逐字一致）
- 函数（原 mutation）：`setConfig`、`upDataName`、`upDataGoodList`、`updataConfig`

### 12. useShoppingStore — `src/diy/store/legacy/shopping.js`（id: `shopping`，**无 persist**）

- state：`activeName`、`defaultConfig`、`component`（内容与原版逐字一致）
- 函数（原 mutation）：`setConfig`、`updataConfig`

### 13. useFreshStore — `src/diy/store/legacy/fresh.js`（id: `fresh`，**无 persist**）

- state：`activeName`、`defaultConfig`、`component`（内容与原版逐字一致）
- 函数（原 mutation）：`setConfig`、`updataConfig`

### 14. useIntegralOrderStore — `src/store/module/integralOrder.js`（id: `integralOrder`，persist: true）

- state：`orderStatus`、`orderTime`、`orderNum`、`orderType`、`fieldKey`、`orderChartType`、`isDels`、`delIdList`、`iconsaaaa`、`orderPayType`
- 函数（原 mutation）：`getOrderStatus`、`getOrderType`、`getOrderTime`、`getOrderNum`、`getfieldKey`、`onChangeTabs`、`onChangeChart`、`getIsDel`、`getisDelIdListl`
- 函数（原 action）：`getOrderTabs`（直接 `resolve(true)`）、`listenFullscreen`、`toggleFullscreen`

### 15. useMobildConfigStore — `src/diy/store/mobildConfig.js`（id: `mobildConfig`，persist: true）

- state：`configName`、`pageTitle`、`pageName`、`pageShow`、`pageColor`、`pagePic`、`pageColorPicker`、`pageTabVal`、`pagePicUrl`、`defaultArray`、`bottomMenu`、`pageFooter`（默认值与原版逐字一致）
- 函数（原 mutation，全部保名）：`FOOTER`、`UPBOTTOMMENU`、`ADDARRAY`、`DELETEARRAY`、`ARRAYREAST`、`defaultArraySort`、`UPDATEARR`、`SETCONFIGNAME`、`SETEMPTY`、`UPTITLE`、`UPNAME`、`UPSHOW`、`UPCOLOR`、`UPPIC`、`UPPICKER`、`UPRADIO`、`UPPICURL`、`footUpdata`、`footStatus`（空实现，原即空）、`footType`、`footBottom`、`footPageUpdata`、`bottomMenuUpdata`、`RESET_BOTTOM_MENU`、`titleUpdata`、`nameUpdata`、`showUpdata`、`colorUpdata`、`picUpdata`、`pickerUpdata`、`radioUpdata`、`picurlUpdata`
- 函数（原 action）：`getData`（空实现）
- 模块级默认模板变量改名 `defaultBottomMenu`（见备注 4）

### 16. useUpgradeStore — `src/store/module/upgrade.js`（id: `upgrade`，persist: true）

- state：`toggleStatus`
- 函数（原 mutation）：`TOGGLE_STATUS`
- 原 getter `toggleStatus` 为恒等 getter，直接使用 `useUpgradeStore().toggleStatus`

### 17. useLayoutStore — `src/store/module/layout.js`（id: `layout`，persist: true）

- state：`menuCollapse`
- 函数（原 mutation）：`changeCol`

### 18. useThemeConfigStore — `src/store/module/themeConfig.js`（id: `themeConfig`，persist: true）

- state：`themeConfig`（默认值与原版逐字一致）
- 函数（原 mutation）：`getThemeConfig(data)`（整体替换）
- 函数（原 action）：`setThemeConfig(data)`

### 19. useRoutesListStore — `src/store/module/routesList.js`（id: `routesList`，persist: true）

- state：`routesList`
- 函数（原 mutation）：`getRoutesList`
- 函数（原 action）：`setRoutesList`

### 20. useTagsViewRoutesStore — `src/store/module/tagsViewRoutes.js`（id: `tagsViewRoutes`，**无 persist**）

- state：`tagsViewRoutes`
- 函数（原 mutation）：`getTagsViewRoutes`
- 函数（原 action）：`setTagsViewRoutes`

### 21. useKeepAliveNamesStore — `src/store/module/keepAliveNames.js`（id: `keepAliveNames`，persist: true）

- state：`keepAliveNames`
- 函数（原 mutation）：`getCacheKeepAlive`
- 函数（原 action）：`setCacheKeepAlive`

### 22. useKefuStore — `src/pages/plugin/kefu/store.js`（id: `kefu`，persist: true）

- state：`kefuInfo`
- 函数（原 mutation）：`setInfo`
- 函数（原 action）：`logoutKefu({ confirm, vm })`
- 原在 `src/store/index.js` 注册，现独立导出 `useKefuStore`，使用方直接 `import { useKefuStore } from '@/pages/plugin/kefu/store'`

## DIY 组件聚合器（require.context → import.meta.glob）

`src/diy/components/legacy/diyComponents/index.js`、`src/diy/components/mobilePage/index.js`、`src/diy/components/mobileConfig/index.js`、`src/diy/components/mobileConfigRight/index.js` 四个文件均已改为：

```js
const modules = import.meta.glob('./*.vue', { eager: true });

const obj = {};
Object.keys(modules).forEach((key) => {
  obj[key.replace(/(\.\/|\.vue)/g, '')] = modules[key].default;
});
export default obj;
```

键名规则与原 `path.basename(key, '.vue')` 完全一致：`./c_is_show.vue` → `c_is_show`。原四个文件均为**非递归**扫描（`require.context('./', false, ...)`），故 glob 用 `./*.vue`（不含子目录），导出名/默认导出结构不变。
