<template>
  <div class="diy-page">
    <el-card :bordered="false" shadow="never">
      <div class="diy-wrapper" :style="'height:' + clientHeight + 'px;'">
        <DiyLeftPalette
          :left-menu="leftMenu"
          :height="clientHeight"
          :clone-dog="cloneDog"
          @pick="(el) => addDom(el, 1)"
        />
        <DiyCanvasPreview
          :m-config="mConfig"
          :roll-height="rollHeight"
          :page-type="pageType"
          :is-micro-page="isMicroPage"
          :page-id="pageId"
          :title-txt="titleTxt"
          :active-index="activeIndex"
          :color-style="colorStyle"
          :props-obj="propsObj"
          :default-arrays="defaultArrays"
          :color-txt="colorTxt"
          :color-picker-txt="colorPickerTxt"
          :pic-txt="picTxt"
          :tab-val-txt="tabValTxt"
          :pic-url-txt="picUrlTxt"
          :page-footer-type="pageFooterType"
          :page-footer-bottom="pageFooterBottom"
          :resolve-comp="resolveComp"
          :is-comp-disabled="isCompDisabled"
          :get-badge-text="getBadgeText"
          :on-move="onMove"
          @drag-change="log"
          @select-title="showTitle"
          @select-foot="showFoot"
          @select-bottom-menu="showBottomMenu"
          @select-item="bindconfig"
          @hide-item="bindHide"
          @delete-item="bindDelete"
          @duplicate-item="bindAddDom"
          @move-item="movePage"
          @open-page-settings="showTitle"
          @open-save-modal="nameModal = true"
          @reset="reast"
          @save-cover="saveCover"
        />
        <DiyRightConfigPanel
          :items="rConfig"
          :active-index="activeIndex"
          :resolve-comp="resolveComp"
          :get-right-config-key="getRightConfigKey"
          :can-render-right-config="canRenderRightConfig"
          @config="config"
        />
      </div>
    </el-card>
    <DiyPreviewDialog ref="previewDialogRef" v-model="modal" :qrcode-img="qrcodeImg" />
    <DiySaveNameDialog v-model="nameModal" v-model:save-name="saveName" @confirm="saveModal" />
  </div>
</template>
<script setup>
import __asset_foot_005_0 from '@diy/assets/images/foot-005.png';
import __asset_foot_006_1 from '@diy/assets/images/foot-006.png';

import {
  ref,
  reactive,
  computed,
  watch,
  inject,
  nextTick,
  onBeforeUnmount,
  onUnmounted,
  getCurrentInstance,
} from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  categoryList as categoryListApi,
  themeInfo,
  themeSave,
  setDefault,
  recovery,
  diyUpdateName,
  getThemeRoutineCode,
  saveThemeImage,
  getColorChange,
  getDiyComponentStatus,
} from '@diy/api';
import { fileUpload } from '@/api/setting';
import mPage, { registerDiyMobilePageGlobals } from '@diy/components/mobilePage/index.js';
import mConfigCom from '@diy/components/mobileConfig/index.js';
import html2canvas from 'html2canvas';
import QRCode from '@/vendor/qrcodejs2';
import Setting from '@/setting';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import DiyLeftPalette from './components/DiyLeftPalette.vue';
import DiyCanvasPreview from './components/DiyCanvasPreview.vue';
import DiyRightConfigPanel from './components/DiyRightConfigPanel.vue';
import DiyPreviewDialog from './components/DiyPreviewDialog.vue';
import DiySaveNameDialog from './components/DiySaveNameDialog.vue';
import {
  objToArr,
  swapArray,
  getBadgeText,
  cloneThemeValue,
  cloneThemeComponent,
  removeThemeComponent,
  warnDuplicateDefaultNames,
} from './utils/diyHelpers';
import {
  convertImagesToBase64,
  preserveCustomTextBackgrounds,
  flattenMonochromeGradients,
} from './utils/coverScreenshotDom';

defineOptions({ name: 'index.vue' });

const reload = inject('reload');
const setDirty = inject('setDirty');

const route = useRoute();
const router = useRouter();
registerDiyMobilePageGlobals(getCurrentInstance().appContext.app);
const mobildConfigStore = useMobildConfigStore();

const {
  pageShow: showTxt,
  pageColor: colorTxt,
  pagePic: bgPic,
  pagePic: picTxt,
  pageColorPicker: colorPickerTxt,
  pageTabVal: tabValTxt,
  pagePicUrl: picUrlTxt,
  defaultArray: defaultArrays,
} = storeToRefs(mobildConfigStore);

const titleTxt = computed(() => mobildConfigStore.pageTitle || '首页');
const pageFooterType = computed(() => mobildConfigStore.pageFooter.navConfig.tabVal || 0);
const pageFooterBottom = computed(() => mobildConfigStore.pageFooter.mbConfig.val);
const nameTxt = computed({
  get() {
    return mobildConfigStore.pageName;
  },
  set(value) {
    mobildConfigStore.UPNAME(value);
  },
});
const isMicroPage = computed(() => route.query.page_type == 'micro');

const BaseURL = Setting.apiBaseURL.replace(/adminapi/, '');
const qrcodeImg = ref('');
const modal = ref(false);
const clientHeight = ref(''); //页面动态高度
const rollHeight = ref('');
const leftMenu = ref([]); // 左侧菜单
const lConfig = ref([]); // 左侧组件
// 后端下发的可用组件白名单（组件数据名 => 组件标题），由 diy/component_status 提供；
// 白名单口径：插件代码被裁剪、未安装或未启用时组件不在名单内，fail-closed 隐藏
const availableComponents = ref({});
// 当前构建中插件归属组件的数据名集合（组件源码 pluginComponent: true 标记）；
// 画布存量节点无标记字段，统一按该集合识别归属，集合外的核心组件恒可用
const pluginComponentNames = new Set(
  Object.values(mPage)
    .filter((comp) => comp && comp.pluginComponent && comp.defaultName)
    .map((comp) => comp.defaultName),
);
function isCompDisabled(item) {
  const name = item && item.defaultName;
  if (!name || !pluginComponentNames.has(name)) return false;
  return !availableComponents.value[name];
}
const mConfig = ref([]); // 中间组件渲染
const rConfig = ref([]); // 右侧组件配置
const activeConfigName = ref('');
const propsObj = reactive({}); // 组件传递的数据,
const activeIndex = ref(-100); // 选中的下标
const number = ref(0);
const pageId = ref('');
const pageName = ref('');
const pageType = ref('');
const category = ref([]);
const tabList = ref([
  {
    title: '组件库',
    key: 0,
  },
  {
    title: '页面链接',
    key: 1,
  },
]);
const footActive = ref(false);
const loading = ref(false);
const relLoading = ref(false);
const isSearch = ref(false);
const isTab = ref(false);
const isFllow = ref(false);
const isComb = ref(false);
const isService = ref(false);
const visible = ref(true);
const diyStatus = ref(0);
const nameModal = ref(false);
const saveName = ref('');
const coverUrl = ref(''); // 原 Options 里未声明的动态属性（saveCover 写入、diySaveDate 读取），显式声明
const previewDialogRef = ref(null);

// 画布/右侧面板 :is 兜底与挂载时机防御（参照 single_admin 迁移修复）
const allComponents = { ...mPage, ...mConfigCom };
function resolveComp(name) {
  return allComponents[name] || name;
}
// 右侧面板 key 含 defaultArray 该组件的字段数：配置从无到有时强制重建面板，
// 避免面板以空 configObj 挂载后控件不刷新
function getRightConfigKey(item) {
  const currentConfig = defaultArrays.value[item.num] || {};
  return `${item.configName}-${item.num}-${Object.keys(currentConfig).length}`;
}
// defaultArray 尚未写入该组件配置时不渲染面板，等数据就绪再挂载
function canRenderRightConfig(item) {
  if (!item || item.num === undefined) return true;
  if (item.configName === 'c_custom_component') return true;
  const currentConfig = defaultArrays.value[item.num];
  return !!(currentConfig && currentConfig.setUp);
}

// ---- @diy/mixins/theme 内联（原 mixins: [theme]，created 时机拉取主题色） ----
const current = ref(3);
const colorStyle = ref({});
getThemeInfo();
// ---- theme 内联结束 ----

// 原 filters 选项，转为普通函数
function filterTxt(val) {
  if (val) {
    return (val = val.substr(0, val.length - 1));
  }
}

// vuedraggable item-key 统一走 utils/draggableKey 的稳定 key（勿用 num 等会被改写的业务字段）

// beforeCreate：清空默认配置
mobildConfigStore.SETEMPTY();

// created
categoryList();
pageId.value = route.query.id;
pageName.value = route.query.name;
pageType.value = route.query.type;
lConfig.value = objToArr(mPage);
// 拉取可用组件白名单（后端判定），返回后重算左侧分组放行插件归属组件；
// 仅影响组件库面板，画布回显与已保存数据节点不受影响（兼容存量 DIY 数据）
getDiyComponentStatus()
  .then((res) => {
    availableComponents.value = res.data?.available_components || {};
    arraySort();
  })
  .catch(() => {
    // 接口异常时白名单置空，插件归属组件一律隐藏（与代码缺失同向失败，宁可不可见不可悬空）
    availableComponents.value = {};
    arraySort();
  });
warnDuplicateDefaultNames(lConfig.value);
let imgList = {
  imgList: [__asset_foot_005_0, __asset_foot_006_1],
  name: '购物车',
  link: '/pages/order_addcart/order_addcart',
};
nextTick(() => {
  mobildConfigStore.FOOTER({ title: '是否自定义', name: imgList });
  arraySort();
  if (pageId.value != 0 || route.query.tid) {
    getDefaultConfig();
  } else if (pageType.value == 'home') {
    showTitle();
  } else {
    // 清空 defaultArray（原 commit('mobildConfig/DEFAULTARRAY') 在 Pinia store 中无同名函数，SETEMPTY 等效清空）
    mobildConfigStore.SETEMPTY();
    mobildConfigStore.RESET_BOTTOM_MENU();
  }
  clientHeight.value = `${document.documentElement.clientHeight}` - 65.81; //获取浏览器可视区域高度
  let H = `${document.documentElement.clientHeight}` - 180;
  rollHeight.value = H > 650 ? 650 : H;
  window.onresize = function () {
    clientHeight.value = `${document.documentElement.clientHeight}` - 65.81;
    let H = `${document.documentElement.clientHeight}` - 180;
    rollHeight.value = H > 650 ? 650 : H;
  };
});

watch(
  mConfig,
  (nVal, oVal) => {
    if (setDirty) {
      setDirty(true);
    }
  },
  { deep: true },
);
watch(
  () => mConfig.value.length,
  (nVal, oVal) => {
    if (setDirty) {
      setDirty(true);
    }
  },
);
watch(
  defaultArrays,
  (nVal, oVal) => {
    if (setDirty) {
      setDirty(true);
    }
  },
  { deep: true },
);

function exportView() {
  loading.value = true;
  nextTick(() => {
    console.log(mConfig.value);
  });
}
function importView() {}
// 保存为封面 - 截取长图并上传
function saveCover() {
  // 保存当前选中的组件索引
  const previousActiveIndex = activeIndex.value;
  ElMessage.info('正在生成封面图片，请稍候...');

  setTimeout(async () => {
    let restoreScreenshotStyles = () => {};
    try {
      // 取消选中状态
      activeIndex.value = -999;
      rConfig.value = [];

      // 等待 DOM 更新
      await nextTick();

      // 获取 imgContainer 元素
      const container = document.getElementById('imgContainer');
      if (!container) {
        ElMessage.error('未找到页面容器元素');
        // 恢复选中状态
        activeIndex.value = previousActiveIndex;
        restoreActiveComponent(previousActiveIndex);
        return;
      }

      // 将容器内所有远程图片（含OSS）预先转为 base64，解决 html2canvas 跨域白图问题
      await convertImagesToBase64(container);

      const restoreTextBackgrounds = preserveCustomTextBackgrounds(container);

      // html2canvas 存在绘制顺序缺陷：组件卡片上「各停点同色」的退化渐变背景
      // （如 linear-gradient(#fff,#fff)）会被画在后代文字之后，封面中导航组
      // （宫格展示）等组件的文字因此被白色背景盖住而"消失"。
      // 截图前临时移除这类背景图，截图完成后恢复；多色渐变属于正常样式，不受影响。
      const restoreGradients = flattenMonochromeGradients(container);
      restoreScreenshotStyles = () => {
        restoreGradients();
        restoreTextBackgrounds();
        restoreScreenshotStyles = () => {};
      };

      // 使用 html2canvas 截取长图
      const canvas = await html2canvas(container, {
        useCORS: true,
        allowTaint: true, // 已手动转为 base64，可放开 allowTaint
        logging: false,
        scale: 2, // 提高清晰度
        backgroundColor: colorTxt.value ? colorPickerTxt.value : '#ffffff',
        scrollY: -window.scrollY,
        scrollX: -window.scrollX,
        windowWidth: container.scrollWidth,
        windowHeight: container.scrollHeight,
      });

      restoreScreenshotStyles();

      // 将 canvas 转换为 blob
      canvas.toBlob(
        async (blob) => {
          if (!blob) {
            ElMessage.error('图片生成失败');
            // 恢复选中状态
            activeIndex.value = previousActiveIndex;
            restoreActiveComponent(previousActiveIndex);
            return;
          }

          // 创建 FormData 上传
          const formData = new FormData();
          const fileName = `cover_${Date.now()}.png`;
          const file = new File([blob], fileName, { type: 'image/png' });
          formData.append('file', file);
          formData.append('type', 1);

          try {
            // 上传到后端
            const res = await fileUpload(formData);
            if (res.status === 200) {
              ElMessage.success('封面保存成功！');
              // 这里可以根据需要将图片URL保存到页面配置中
              coverUrl.value = res.data.src;
              if (!mConfig.value.length) return ElMessage.warning('请先配置DIY数据');
              if (!pageId.value) return ElMessage.warning('请先保存DIY数据');
              saveThemeImage(pageId.value, { image: coverUrl.value, type: pageType.value });
            } else {
              ElMessage.error(res.msg || '封面保存失败');
            }
          } catch (err) {
            ElMessage.error(err.msg || '上传失败，请重试');
          } finally {
            // 恢复选中状态
            activeIndex.value = previousActiveIndex;
            restoreActiveComponent(previousActiveIndex);
          }
        },
        'image/png',
        0.95,
      ); // 图片质量 0.95
    } catch (error) {
      restoreScreenshotStyles();
      console.error('截图失败：', error);
      ElMessage.error('生成封面失败，请重试');
      // 恢复选中状态
      activeIndex.value = previousActiveIndex;
      restoreActiveComponent(previousActiveIndex);
    }
  }, 300);
}
// 恢复选中的组件
function restoreActiveComponent(index) {
  if (index === -100) {
    // 恢复页面设置
    showTitle();
  } else if (index === -101) {
    // 恢复底部菜单
    showFoot();
  } else if (index === -102) {
    // 恢复底部菜单
    showBottomMenu();
  } else if (index >= 0 && index < mConfig.value.length) {
    // 恢复组件选中
    const item = mConfig.value[index];
    bindconfig(item, index);
  }
}
function preview() {
  modal.value = true;
  // 弹窗 DOM 首次打开时下一帧才挂载（Element Plus teleport 懒渲染），qrCodeUrl ref 需等 nextTick
  nextTick(() => {
    creatQrCode();
  });
  if (pageId.value) {
    routineCode(pageId.value);
  } else {
    qrcodeImg.value = '';
  }
}
//小程序二维码（装修编辑器保存于 eb_theme，走主题预览码接口）
function routineCode(id) {
  getThemeRoutineCode(id, isMicroPage.value ? 'micro' : 'home')
    .then((res) => {
      qrcodeImg.value = res.data.image || '';
    })
    .catch((err) => {
      qrcodeImg.value = '';
      ElMessage.error(err?.msg || err?.message || '获取小程序预览码失败');
    });
}
//生成二维码
function creatQrCode() {
  const raw = previewDialogRef.value?.qrCodeUrl;
  const el = raw && raw.__v_isRef ? raw.value : raw;
  if (!el) return;
  el.innerHTML = '';
  // 微页面预览页与首页均通过 theme_id 场景值解析主题（见 uni-app 对应页面 onLoad）
  let path = isMicroPage.value ? 'pages/annex/special/index' : 'pages/index/index';
  let url = `${BaseURL}${path}?theme_id=${route.query.id}`;
  var qrcode = new QRCode(el, {
    text: url, // 需要转换为二维码的内容
    width: 160,
    height: 160,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}
function changName(val) {
  mobildConfigStore.UPNAME(val);
}
function cancel() {
  visible.value = false;
}
function determine() {
  if (nameTxt.value.trim() == '') {
    return ElMessage.error('请输入模板名称');
  }
  if (pageId.value == 0) {
    ElMessage.success('修改成功');
    return false;
  }
  diyUpdateName(pageId.value, { name: nameTxt.value })
    .then((res) => {
      visible.value = false;
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
  visible.value = false;
}
function returnTap() {
  ElMessageBox({
    title: '温馨提示',
    message: '确定离开此页面？系统可能不会保存您所做的更改。',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
    showClose: false,
  })
    .then(() => {
      router.push(Setting.routePre + '/setting/pages/devise/0');
    })
    .catch(() => {});
}
function leftRemove({ to, from, item, clone, oldIndex, newIndex }) {
  if (isSearch.value && newIndex == 0) {
    if (item._underlying_vm_.name == 'z_wechat_attention') {
      isFllow.value = true;
    } else {
      mobildConfigStore.ARRAYREAST(mConfig.value[0].num);
      mConfig.value.splice(0, 1);
    }
  }
  if ((isFllow.value = true && newIndex >= 1)) {
    mobildConfigStore.ARRAYREAST(mConfig.value[0].num);
  }
}
function onMove(e) {
  if (e.relatedContext.element.name == 'search_box') return false;
  if (e.relatedContext.element.name == 'nav_bar') return false;
  if (e.relatedContext.element.name == 'home_comb') return false;
  return true;
}
function onCopy() {
  ElMessage.success('复制成功');
}
function onError() {
  ElMessage.error('复制失败');
}
function checkCanAddComponent(item) {
  if (item.name == 'search_box') {
    if (isSearch.value) {
      ElMessage.error('该组件只能添加一次');
      return false;
    }
    if (isComb.value) {
      ElMessage.error('轮播搜索不能和搜索组件与选项卡组件同时存在');
      return false;
    }
  }
  if (item.name == 'nav_bar') {
    if (isTab.value) {
      ElMessage.error('该组件只能添加一次');
      return false;
    }
    if (isComb.value) {
      ElMessage.error('轮播搜索不能和搜索组件与选项卡组件同时存在');
      return false;
    }
  }
  if (item.name == 'home_comb') {
    if (isComb.value) {
      ElMessage.error('该组件只能添加一次');
      return false;
    }
    if (isSearch.value || isTab.value) {
      ElMessage.error('轮播搜索不能和搜索组件与选项卡组件同时存在');
      return false;
    }
  }
  if (item.name == 'home_service' && isService.value) {
    ElMessage.error('该组件只能添加一次');
    return false;
  }
  return true;
}
function updateSpecialComponentStatus(name, status) {
  if (name == 'search_box') {
    isSearch.value = status;
  }
  if (name == 'nav_bar') {
    isTab.value = status;
  }
  if (name == 'home_comb') {
    isComb.value = status;
  }
  if (name == 'home_service') {
    isService.value = status;
  }
}
function appendBottomMenu(value) {
  let bottomMenu = cloneThemeComponent(mobildConfigStore.bottomMenu);
  if (!bottomMenu || bottomMenu.name != 'bottomMenu') return value;
  let timestamp = bottomMenu.timestamp || new Date().getTime() * 1000;
  bottomMenu.timestamp = timestamp;
  bottomMenu.id = bottomMenu.id || 'id' + timestamp;
  value[timestamp] = bottomMenu;
  return value;
}
function appendPageFooter(value) {
  let pageFooter = cloneThemeComponent(mobildConfigStore.pageFooter);
  if (!pageFooter || pageFooter.name != 'pageFoot') return value;
  removeThemeComponent(value, 'pageFoot');
  let timestamp = pageFooter.timestamp || new Date().getTime() * 1000;
  pageFooter.timestamp = timestamp;
  pageFooter.id = pageFooter.id || 'id' + timestamp;
  value[timestamp] = pageFooter;
  return value;
}
//设置默认数据
function setmoren() {
  setDefault(pageId.value)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
//恢复默认
function getmoren() {
  recovery(pageId.value)
    .then((res) => {
      ElMessage.success(res.msg);
      reload();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// 页面标题点击
function showTitle() {
  activeIndex.value = -100;
  let obj = {};
  for (var i in mConfigCom) {
    if (i == 'pageTitle') {
      // rConfig.value = obj
      obj = mConfigCom[i];
      obj.configName = mConfigCom[i].name;
      obj.cname = '页面设置';
    }
  }
  rConfig.value = [];
  rConfig.value[0] = JSON.parse(JSON.stringify(obj));
}
// 页面底部点击
function showFoot() {
  activeIndex.value = -101;
  let obj = {};
  for (var i in mConfigCom) {
    if (i == 'pageFoot') {
      // rConfig.value = obj
      obj = mConfigCom[i];
      obj.configName = mConfigCom[i].name;
      obj.cname = '底部菜单';
    }
  }
  rConfig.value = [];
  rConfig.value[0] = JSON.parse(JSON.stringify(obj));
}
function showBottomMenu() {
  activeIndex.value = -102;
  let obj = {};
  for (var i in mConfigCom) {
    if (i == 'c_bottom_menu') {
      // rConfig.value = obj
      obj = mConfigCom[i];
      obj.configName = mConfigCom[i].name;
      obj.cname = '底部菜单';
    }
  }
  rConfig.value = [];
  rConfig.value.push(JSON.parse(JSON.stringify(obj)));
}
function log(evt) {
  // 中间拖拽排序
  if (evt.moved) {
    if (evt.moved.element.name == 'search_box') {
      return ElMessage.warning('该组件禁止拖拽');
    }
    // if (evt.moved.element.name == "nav_bar") {
    //     return ElMessage.warning("该组件禁止拖拽");
    // }
    evt.moved.oldNum = mConfig.value[evt.moved.oldIndex].num;
    evt.moved.newNum = mConfig.value[evt.moved.newIndex].num;
    evt.moved.status = evt.moved.oldIndex > evt.moved.newIndex;
    mConfig.value.forEach((el, index) => {
      el.num = new Date().getTime() * 1000 + index;
    });
    evt.moved.list = mConfig.value;
    rConfig.value = [];
    let item = evt.moved.element;
    let tempItem = JSON.parse(JSON.stringify(item));
    rConfig.value.push(tempItem);
    activeIndex.value = evt.moved.newIndex;
    mobildConfigStore.SETCONFIGNAME(item.name);
    mobildConfigStore.defaultArraySort(evt.moved);
  }
  // 从左向右拖拽排序
  if (evt.added) {
    let data = evt.added.element;
    if (!checkCanAddComponent(data)) {
      mConfig.value.splice(evt.added.newIndex, 1);
      return;
    }
    let obj = {};
    let timestamp = new Date().getTime() * 1000;
    data.num = timestamp;
    activeConfigName.value = data.name;
    let tempItem = JSON.parse(JSON.stringify(data));
    tempItem.id = 'id' + tempItem.num;
    mConfig.value[evt.added.newIndex] = tempItem;
    rConfig.value = [];
    rConfig.value.push(tempItem);
    updateSpecialComponentStatus(data.name, true);
    mConfig.value.forEach((el, index) => {
      el.num = new Date().getTime() * 1000 + index;
    });
    evt.added.list = mConfig.value;
    activeIndex.value = evt.added.newIndex;
    // 保存组件名称
    mobildConfigStore.SETCONFIGNAME(data.name);
    mobildConfigStore.defaultArraySort(evt.added);
  }
}
function cloneDog(data) {
  // mConfig.value.push(tempItem)
  return {
    ...data,
  };
}
//数组元素互换位置
//点击上下移动；
function movePage(item, index, type) {
  if (type) {
    if (index == 0) {
      return;
    }
  } else {
    if (index == mConfig.value.length - 1) {
      return;
    }
  }
  if (item.name == 'search_box' || item.name == 'nav_bar' || item.name == 'home_comb') {
    return ElMessage.warning('该组件禁止移动');
  }
  if (type) {
    if (
      mConfig.value[index - 1].name == 'search_box' ||
      mConfig.value[index - 1].name == 'nav_bar' ||
      mConfig.value[index - 1].name == 'home_comb'
    ) {
      return ElMessage.warning('搜索框或选项卡或轮播搜索必须为顶部');
    }
    swapArray(mConfig.value, index - 1, index);
  } else {
    swapArray(mConfig.value, index, index + 1);
  }
  let obj = {};
  rConfig.value = [];
  obj.oldIndex = index;
  if (type) {
    obj.newIndex = index - 1;
  } else {
    obj.newIndex = index + 1;
  }
  mConfig.value.forEach((el, index) => {
    el.num = new Date().getTime() * 1000 + index;
  });
  let tempItem = JSON.parse(JSON.stringify(item));
  rConfig.value.push(tempItem);
  obj.element = item;
  obj.list = mConfig.value;
  if (type) {
    activeIndex.value = index - 1;
  } else {
    activeIndex.value = index + 1;
  }

  mobildConfigStore.SETCONFIGNAME(item.name);
  mobildConfigStore.defaultArraySort(obj);
}
// 组件添加
function addDomCon(item, type, index) {
  if (!checkCanAddComponent(item)) {
    return false;
  }
  updateSpecialComponentStatus(item.name, true);
  let obj = {};
  let timestamp = new Date().getTime() * 1000;
  item.num = `${timestamp}`;
  item.id = `id${timestamp}`;
  activeConfigName.value = item.name;
  let tempItem = JSON.parse(JSON.stringify(item));
  if (item.name == 'home_comb') {
    rConfig.value = [];
    mConfig.value.unshift(tempItem);
    activeIndex.value = 0;
    rConfig.value.push(tempItem);
  } else if (item.name == 'search_box') {
    rConfig.value = [];
    mConfig.value.unshift(tempItem);
    activeIndex.value = 0;
    rConfig.value.push(tempItem);
  } else if (item.name == 'nav_bar') {
    rConfig.value = [];
    if (mConfig.value[0] && mConfig.value[0].name === 'search_box') {
      mConfig.value.splice(1, 0, tempItem);
      activeIndex.value = 1;
    } else {
      mConfig.value.splice(0, 0, tempItem);
      activeIndex.value = 0;
    }
    rConfig.value.push(tempItem);
  } else {
    if (type) {
      rConfig.value = [];
      if (activeIndex.value == 0 && mConfig.value[1] && mConfig.value[1].name == 'nav_bar') {
        activeIndex.value = 2;
      } else {
        activeIndex.value = activeIndex.value >= 0 ? activeIndex.value + 1 : mConfig.value.length;
      }
      mConfig.value.splice(activeIndex.value, 0, tempItem);
      rConfig.value.push(tempItem);
    } else {
      mConfig.value.splice(index + 1, 0, tempItem);
      activeIndex.value = index;
    }
  }
  mConfig.value.forEach((el, index) => {
    el.num = new Date().getTime() * 1000 + index;
  });
  // 保存组件名称
  obj.element = item;
  obj.list = mConfig.value;
  mobildConfigStore.SETCONFIGNAME(item.name);
  mobildConfigStore.defaultArraySort(obj);
  return true;
}
//中间页点击添加模块；
function bindAddDom(item, type, index) {
  // 复制
  if (type == 0) {
    let defaultArray = mobildConfigStore.defaultArray;
    let configData = JSON.parse(JSON.stringify(defaultArray[item.num]));

    // 找到原始配置以获取 cname, icon 等基础信息
    let baseItem = item;
    lConfig.value.forEach((j) => {
      if (item.name == j.name) {
        baseItem = j;
      }
    });

    // 先调用 addDomCon 添加一个新组件，然后立即用 configData 覆盖它
    if (!addDomCon(baseItem, type, index)) {
      return;
    }

    // 获取刚添加的组件（在 index+1 位置，因为 addDomCon 是 splice(index+1, 0, ...)）
    let newIndex = index + 1;
    let newItem = mConfig.value[newIndex];

    // 重新获取最新的 defaultArray (引用)
    let currentDefaultArray = mobildConfigStore.defaultArray;

    // 保留新生成的 num 和 timestamp 相关字段，覆盖其他配置
    let newConfig = {
      ...configData,
      num: newItem.num,
      id: newItem.id, // 确保使用新生成的唯一ID
      timestamp: currentDefaultArray[newItem.num].timestamp,
    };

    // 提交更新
    mobildConfigStore.UPDATEARR({ num: newItem.num, val: newConfig });
  } else {
    let i = item;
    lConfig.value.forEach((j) => {
      if (item.name == j.name) {
        i = j;
      }
    });
    if (!addDomCon(i, type, index)) {
      return;
    }
  }
}
//左边配置模块点击添加；
function addDom(item, type) {
  addDomCon(item, type);
}
// 点击显示相应的配置
function bindconfig(item, index) {
  // 不可用组件仅作占位展示，禁止进入右侧配置面板，避免基于不可用状态误改配置
  if (isCompDisabled(item)) return;
  rConfig.value = [];
  let tempItem = JSON.parse(JSON.stringify(item));
  rConfig.value.push(tempItem);
  activeIndex.value = index;
  mobildConfigStore.SETCONFIGNAME(item.name);
}
function bindHide(item) {
  let obj = mobildConfigStore.defaultArray;
  let num = rConfig.value[0].num;
  obj[num].isHide = !obj[num].isHide;
  mobildConfigStore.UPDATEARR({ num: num, val: obj[num] });
}
// 组件删除
function bindDelete(item, key) {
  ElMessageBox.confirm('确定要删除此组件吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  })
    .then(() => {
      updateSpecialComponentStatus(item.name, false);
      mConfig.value.splice(key, 1);
      rConfig.value.splice(0, 1);
      if (mConfig.value.length != key) {
        rConfig.value.push(mConfig.value[key]);
      } else {
        if (mConfig.value.length) {
          activeIndex.value = key - 1;
          rConfig.value.push(mConfig.value[key - 1]);
        } else {
          showTitle();
        }
      }
      // 删除第几个配置
      mobildConfigStore.DELETEARRAY(item);
    })
    .catch(() => {});
}
// 组件返回
function config(data) {
  let propsObjTemp = propsObj;
  propsObjTemp.data = data;
  propsObjTemp.name = activeConfigName.value;
}
function addSort(arr, index1, index2) {
  arr[index1] = arr.splice(index2, 1, arr[index1])[0];
  return arr;
}
// 数组排序
function arraySort() {
  let tempArr = [];
  let basis = {
    title: '基础组件',
    list: [],
    isOpen: true,
  };
  let marketing = {
    title: '营销组件',
    list: [],
    isOpen: true,
  };
  let goods = {
    title: '商品组件',
    list: [],
    isOpen: true,
  };
  let user = {
    title: '用户组件',
    list: [],
    isOpen: true,
  };
  let tool = {
    title: '工具组件',
    list: [],
    isOpen: true,
  };
  lConfig.value.map((el, index) => {
    // 不可用组件不进入左侧组件库面板；画布回显匹配走完整 lConfig，不受影响
    if (isCompDisabled(el)) return;
    if (el.type == 0) {
      basis.list.push(el);
    }
    if (el.type == 1) {
      marketing.list.push(el);
    }
    if (el.type == 2) {
      tool.list.push(el);
    }
    if (el.type == 3) {
      if (pageType.value !== 'home' && pageType.value !== 'user') {
        if (el.name === 'home_product_info' || el.cname === '商品信息') {
          goods.list.unshift(el);
        } else {
          goods.list.push(el);
        }
      }
    }
    if (el.type == 4) {
      if (pageType.value !== 'home' && pageType.value !== 'detail') user.list.push(el);
    }
  });
  tempArr.push(basis, marketing);
  if (pageType.value !== 'home' && pageType.value !== 'user') tempArr.push(goods);
  if (pageType.value !== 'home' && pageType.value !== 'detail') tempArr.push(user);
  tempArr.push(tool);
  leftMenu.value = tempArr;
}
// toImage(val){
//     html2canvas(qrCodeUrl.value,{
//         useCORS:true,
//         logging:true,
//         taintTest: false,
//         backgroundColor: null
//     }).then((canvas) => {
//         let imgUrl = canvas.toDataURL('image/jpeg');
//         diySaveDate(val,imgUrl)
//     });
// }
function diySaveDate(val, num, title, save) {
  let pageData = {};
  if (['home', 'detail', 'user'].includes(pageType.value)) {
    pageData = {
      type: pageType.value,
      value: val,
      title: titleTxt.value,
      name: nameTxt.value || '模板',
      is_show: showTxt.value ? 1 : 0,
      is_bg_color: colorTxt.value ? 1 : 0,
      is_bg_pic: bgPic.value ? 1 : 0,
      color_picker: colorPickerTxt.value,
      bg_tab_val: tabValTxt.value,
      bg_pic: picUrlTxt.value,
      cover_pic: coverUrl.value,
    };
  } else if (['category', 'theme'].includes(pageType.value)) {
    pageData = val;
  }
  let requestData = {
    type: pageType.value,
    value: pageData,
  };
  if (route.query.page_type === 'micro') {
    requestData.page_type = 'micro';
  }
  if (title) {
    requestData.title = title;
  }
  if (route.query.tid) {
    requestData.tid = route.query.tid;
  }
  themeSave(title ? 0 : pageId.value, requestData)
    .then((res) => {
      if (pageId.value != res.data.id && !title) {
        let query = { ...route.query, id: res.data.id };
        delete query.tid; // 保存后移除 tid
        router.replace({ query });
        pageId.value = res.data.id;
      }
      ElMessage.success(res.msg);
      nameModal.value = false;
      if (num == 2) {
        relLoading.value = false;
        setTimeout(() => {
          let page = isMicroPage.value
            ? Setting.routePre + '/setting/theme/micro_page'
            : Setting.routePre + '/setting/my_theme';
          window.location.replace(page);
        }, 2000);
      } else {
        loading.value = false;
      }
      if (setDirty) {
        setDirty(false);
      }
    })
    .catch((res) => {
      relLoading.value = false;
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function saveModal() {
  if (!saveName.value) return ElMessage.warning('请先输入模板名称');
  saveConfig(1, saveName.value);
}
function closeWindow() {
  ElMessageBox({
    title: '提示',
    message: '关闭页面前请先保存数据，未保存的话数据会丢失',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
  })
    .then(() => {
      setTimeout(() => {
        // saveConfig();
        window.close();
      }, 1000);
    })
    .catch(() => {});
}
// 保存配置
function saveConfig(num, type, save) {
  if (mConfig.value.length == 0) {
    return ElMessage.error('暂未添加任何组件，保存失败！');
  }
  if (num == 1) {
    loading.value = true;
  } else {
    relLoading.value = true;
  }
  let val = cloneThemeValue(mobildConfigStore.defaultArray);
  if (pageType.value == 'home') {
    val = appendPageFooter(val);
  } else if (pageType.value == 'detail') {
    val = appendBottomMenu(removeThemeComponent(val, 'bottomMenu'));
  }
  nextTick(() => {
    diySaveDate(val, num, type, save);
  });
}
// 获取默认配置
function getDefaultConfig() {
  let id = pageId.value;
  if (id == 0 && route.query.tid) {
    id = route.query.tid;
  }
  themeInfo(id, pageType.value).then((res) => {
    let data = res.data;
    mobildConfigStore.titleUpdata(data.title);
    mobildConfigStore.nameUpdata(data.name);
    mobildConfigStore.showUpdata(data.is_show);
    mobildConfigStore.colorUpdata(data.is_bg_color || 0);
    mobildConfigStore.picUpdata(data.is_bg_pic || 0);
    mobildConfigStore.pickerUpdata(data.color_picker || '#f5f5f5');
    mobildConfigStore.radioUpdata(data.bg_tab_val || 0);
    mobildConfigStore.picurlUpdata(data.bg_pic || '');
    diyStatus.value = data.status;
    let newArr = objToArr(data.value);

    function sortNumber(a, b) {
      return a.timestamp - b.timestamp;
    }
    newArr.sort(sortNumber);
    let hasBottomMenu = false;
    let hasPageFooter = false;
    newArr.map((el, index) => {
      if (el.name == 'headerSerch') {
        isSearch.value = true;
      }
      if (el.name == 'tabNav') {
        isTab.value = true;
      }
      if (el.name == 'homeComb') {
        isComb.value = true;
      }
      if (el.name == 'customerService') {
        isService.value = true;
      }
      if (el.name == 'goodList') {
        // let storage = window.localStorage;
        // storage.setItem(el.timestamp, el.selectConfig.activeValue);
      }
      if (el.name == 'bottomMenu') {
        hasBottomMenu = true;
        mobildConfigStore.UPBOTTOMMENU(el);
        return;
      }
      if (pageType.value == 'home' && el.name == 'pageFoot') {
        hasPageFooter = true;
        footActive.value = true;
        mobildConfigStore.footPageUpdata(el);
        return;
      }
      el.id = 'id' + el.timestamp;
      let component = lConfig.value.find((item) => el.name == item.defaultName);
      if (!component) return;
      let tempItem = JSON.parse(JSON.stringify(component));
      tempItem.num = el.timestamp;
      tempItem.id = 'id' + el.timestamp;
      mConfig.value.push(tempItem);
      // 保存默认组件配置
      mobildConfigStore.ADDARRAY({
        num: el.timestamp,
        val: el,
      });
    });
    if (pageType.value == 'home' && hasPageFooter) {
      showTitle();
    } else if (pageType.value == 'detail' && !hasBottomMenu) {
      mobildConfigStore.RESET_BOTTOM_MENU();
    }
  });
}
// 原 Vuex 写法 categoryList((res) => {...}) 为回调风格，api 实际返回 Promise、回调不会被调用，保持原行为
function categoryList(cb) {
  categoryListApi(cb).then((res) => {
    category.value = res.data;
  });
}
// 重置
function reast() {
  if (pageId.value == 0) {
    ElMessage.error('新增页面，无法重置');
  } else {
    ElMessageBox.confirm('此操作将清空模板内容, 是否继续?', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }).then((res) => {
      mConfig.value = [];
      rConfig.value = [];
      activeIndex.value = -99;
      getDefaultConfig();
    });
  }
}
// ---- @diy/mixins/theme 内联实现 ----
function getThemeInfo() {
  getColorChange('color_change')
    .then((res) => {
      current.value = res.data.status ? res.data.status : 3;
      let colorList = [
        {
          theme: '#42CA4D',
          priceColor: '#FF7600',
          minorColor: 'rgba(108, 198, 94, 0.5)',
          minorColorT: 'rgba(66, 202, 77, 0.1)',
          bntColor: '#FE960F',
          gradient: '#4DEA4D',
        },
        {
          theme: '#e93323',
          priceColor: '#e93323',
          minorColor: 'rgba(233, 51, 35, 0.5)',
          minorColorT: 'rgba(233, 51, 35, 0.1)',
          bntColor: '#FE960F',
          gradient: '#FF7931',
        },
        {
          theme: '#1DB0FC',
          priceColor: '#FD502F',
          minorColor: 'rgba(58, 139, 236, 0.5)',
          minorColorT: 'rgba(9, 139, 243, 0.1)',
          bntColor: '#22CAFD',
          gradient: '#5ACBFF',
        },
        {
          theme: '#FF448F',
          priceColor: '#FF448F',
          minorColor: 'rgba(255, 68, 143, 0.5)',
          minorColorT: 'rgba(255, 68, 143, 0.1)',
          bntColor: '#282828',
          gradient: '#FF67AD',
        },
        {
          theme: '#FE5C2D',
          priceColor: '#FE5C2D',
          minorColor: 'rgba(254, 92, 45, 0.5)',
          minorColorT: 'rgba(254, 92, 45, 0.1)',
          bntColor: '#FDB000',
          gradient: '#FF9451',
        },
        {
          theme: '#E0A558',
          priceColor: '#DA8C18',
          minorColor: 'rgba(224, 165, 88, 0.5)',
          minorColorT: 'rgba(224, 165, 88, 0.1)',
          bntColor: '#1A1A1A',
          gradient: '#FFCD8C',
        },
      ];
      switch (res.data.status) {
        case 1:
          colorStyle.value = colorList[2];
          break;
        case 2:
          colorStyle.value = colorList[0];
          break;
        case 3:
          colorStyle.value = colorList[1];
          break;
        case 4:
          colorStyle.value = colorList[3];
          break;
        case 5:
          colorStyle.value = colorList[4];
          break;
        case 6:
          colorStyle.value = colorList[5];
          break;
        default:
          colorStyle.value = colorList[1];
          break;
      }
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// ---- theme 内联实现结束 ----

// 供 editTheme 各 Editor 经模板 ref 调用（script setup 组件默认封闭，必须显式暴露）：
// preview/saveConfig（顶栏预览、保存）、nameModal（另存主题弹窗）、pageId（新建主题后回写 id）
defineExpose({
  pageId,
  nameModal,
  preview,
  saveConfig,
});

// 卸载时重置 mobildConfig 页面级字段
function resetPageConfig() {
  mobildConfigStore.titleUpdata('');
  mobildConfigStore.nameUpdata('');
  mobildConfigStore.showUpdata(1);
  mobildConfigStore.colorUpdata(0);
  mobildConfigStore.picUpdata(0);
  mobildConfigStore.pickerUpdata('#f5f5f5');
  mobildConfigStore.radioUpdata(0);
  mobildConfigStore.picurlUpdata('');
  mobildConfigStore.SETEMPTY();
}

onBeforeUnmount(() => {
  resetPageConfig();
});
onUnmounted(() => {
  resetPageConfig();
});
</script>
<style>
.el-main {
  padding: 0px !important;
}
</style>
<style scoped>
.header-title {
  background: var(--prev-color-primary);
  border-radius: 0;
  margin-bottom: 0;
  padding: 16px;
}
.ivu-page-header-title {
  color: #fff;
  font-size: 16px;
}
</style>
<style lang="scss">
@import './diyIndex.scss';
</style>
