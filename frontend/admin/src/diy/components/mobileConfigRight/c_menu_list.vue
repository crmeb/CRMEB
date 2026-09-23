<template>
  <div class="hot_imgs">
    <div class="title" v-if="configData.title">
      {{ configData.title }}
    </div>
    <div
      class="type-switch"
      style="margin-bottom: 15px"
      v-if="
        (defaults.name == 'menus' ||
          defaults.name == 'member' ||
          configNme == 'menuConfig' ||
          configNme == 'shortcutConfig') &&
        configNme != 'assetConfig'
      "
    >
      <div class="type-title">{{ configData.listStyleName || '图文内容' }}</div>
      <el-radio-group v-if="configData.listStyle != -1" v-model="configData.listStyle">
        <el-radio :label="0" :value="0">图片</el-radio>
        <el-radio :label="1" :value="1">图标</el-radio>
      </el-radio-group>
    </div>
    <div class="type-switch" style="margin-bottom: 15px" v-if="configNme == 'assetConfig'">
      <!-- <span class="type-title">展示样式</span> -->
      <el-radio-group v-model="configData.listStyle">
        <el-radio v-if="configData.assetMode == 0" :label="2" :value="2">数字(上)</el-radio>
        <el-radio v-if="configData.assetMode == 0" :label="3" :value="3">数字(左)</el-radio>
        <el-radio :label="0" :value="0">图片</el-radio>
        <el-radio :label="1" :value="1">图标</el-radio>
      </el-radio-group>
    </div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="configData.list"
        group="peoples"
        handle=".move-icon"
        :item-key="menuItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item">
            <div class="delect-btn" @click.stop="bindDelete(item, index)" v-if="!configData.isCube">
              <span class="iconfont icondel_1"></span>
            </div>
            <div class="move-icon">
              <span class="iconfont iconxingzhuangjiehe"></span>
            </div>
            <div
              class="img-box"
              @click="modalPicTap('单选', index)"
              v-if="configData.listStyle == 0 || configData.listStyle == -1"
            >
              <img :src="item.img" alt="" v-if="item.img" />
              <div class="upload-box" v-else>
                <i class="el-icon-plus"></i>
              </div>
            </div>
            <div class="img-box" @click="openIconModal(index)" v-else-if="configData.listStyle == 1">
              <div class="upload-box" v-if="!item.icon">
                <i class="el-icon-plus"></i>
              </div>
              <div
                style="
                  width: 100%;
                  height: 100%;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  background: #fff;
                  border: 1px solid #eee;
                  border-radius: 4px;
                "
                v-else
              >
                <span :class="['mb-iconfont', item.icon]" style="font-size: 30px; color: #666"></span>
              </div>
            </div>
            <div class="info">
              <div class="info-item" v-for="(infos, key) in item.info" :key="key">
                <span class="span">{{ infos.title }}</span>
                <div class="input-box">
                  <el-input v-model="infos.value" :placeholder="infos.tips" :maxlength="infos.max">
                    <template #suffix
                      ><i
                        v-if="infos.title == '链接'"
                        class="el-icon-link"
                        @blur="onBlur"
                        @click="getLink(index, key, item.info)"
                    /></template>
                  </el-input>
                </div>
              </div>
              <div class="info-item" v-if="configData.type && !hideStatusSwitch">
                <span class="span">状态</span>
                <el-switch v-model="item.show" />
              </div>
            </div>
          </div>
        </template>
      </draggable>
      <div>
        <el-dialog v-model="modalPic" width="960px" title="上传图片">
          <uploadPictures
            :isChoice="isChoice"
            @getPic="getPic"
            :gridBtn="gridBtn"
            :gridPic="gridPic"
            v-if="modalPic"
          ></uploadPictures>
        </el-dialog>
      </div>
    </div>
    <template v-if="configData.list">
      <div class="add-btn" v-if="configData.list.length < configData.maxList">
        <el-button class="btn" type="primary" ghost @click="addBox">
          <span class="iconfont iconjiahao"></span>{{ configData.bnt }}
        </el-button>
      </div>
    </template>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
    <c_icon_select ref="iconSelect" @select="handleIconSelect"></c_icon_select>
  </div>
</template>

<script setup>
import iconsPng from '@/assets/images/icons.png';
import { ref, watch, onMounted, nextTick, defineAsyncComponent } from 'vue';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'c_menu_list' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
  index: {
    type: null,
  },
  hideStatusSwitch: {
    type: Boolean,
    default: false,
  },
  emptyItemTemplate: {
    type: Object,
    default: null,
  },
});

const c_icon_select = defineAsyncComponent(() => import('./c_icon_select'));

const defaults = ref({});
const configData = ref({});
const menus = ref([]);
const modalPic = ref(false);
const isChoice = ref('单选');
const gridBtn = ref({
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
});
const gridPic = ref({
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
});
const activeIndex = ref(0);
const indexLast = ref(0);
const lastObj = ref({});

const linkaddres = ref(null);
const iconSelect = ref(null);

// vuedraggable4 item-key（数据无唯一 id，用索引）
function menuItemKey(element) {
  return configData.value.list.indexOf(element);
}

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    if (configData.value && configData.value.listStyle === undefined) {
      configData.value['listStyle'] = 0;
    }
  });
});

watch(
  () => props.configObj,
  (nVal, oVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    if (configData.value && configData.value.listStyle === undefined) {
      configData.value['listStyle'] = 0;
    }
  },
  { deep: true },
);

function linkUrl(e) {
  configData.value.list[activeIndex.value].info[indexLast.value].value = e;
  if (defaults.value.name == 'pictureCube') {
    defaults.value.picStyle.picList[defaults.value.picStyle.tabVal].link = e;
  }
}
function getLink(index, key, item) {
  indexLast.value = item.length - 1;
  // if (key != item.length - 1) {
  //   return;
  // }
  activeIndex.value = index;
  linkaddres.value.modals = true;
}
function addBox() {
  const lastItem = configData.value.list[configData.value.list.length - 1];
  const template = lastItem || (lastObj.value.info ? lastObj.value : props.emptyItemTemplate);

  if (!template) return;

  const item = JSON.parse(JSON.stringify(template));
  item.img = '';
  item.type = 0;
  item.icon = '';
  if (Array.isArray(item.info)) {
    item.info.forEach((info) => {
      info.value = '';
    });
  }
  configData.value.list.push(item);
}
// 点击图文封面
function modalPicTap(title, index) {
  activeIndex.value = index;
  modalPic.value = true;
}
function openIconModal(index) {
  activeIndex.value = index;
  iconSelect.value.show();
}
function handleIconSelect(icon) {
  configData.value.list[activeIndex.value]['icon'] = icon;
}
// 添加自定义弹窗
function addCustomDialog(editorId) {
  window.UE.registerUI(
    'test-dialog',
    function (editor, uiName) {
      let dialog = new window.UE.ui.Dialog({
        iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
        editor: editor,
        name: uiName,
        title: '上传图片',
        cssRules: 'width:1200px;height:500px;padding:20px;',
      });
      // 参考上面的自定义按钮
      var btn = new window.UE.ui.Button({
        name: 'dialog-button',
        title: '上传图片',
        cssRules: `background-image: url(${iconsPng});background-position: -726px -77px;`,
        onclick: function () {
          // 渲染dialog
          dialog.render();
          dialog.open();
        },
      });

      return btn;
    },
    37,
  );
}
// 获取图片信息
function getPic(pc) {
  nextTick(() => {
    configData.value.list[activeIndex.value].img = pc.att_dir;
    let data = defaults.value.menuConfig;
    if (data && data.isCube) {
      defaults.value.picStyle.picList.splice(defaults.value.picStyle.tabVal, 1, {
        image: pc.att_dir,
        link: data.list[0].info[0].value,
      });
    }
    modalPic.value = false;
  });
}
function onBlur() {
  let data = defaults.value.menuConfig;
  defaults.value.picStyle.picList[defaults.value.picStyle.tabVal].link = data.list[0].info[0].value;
}
// 删除
function bindDelete(item, index) {
  if (configData.value.list.length == 1) {
    lastObj.value = JSON.parse(JSON.stringify(configData.value.list[0]));
  }
  configData.value.list.splice(index, 1);
}
</script>

<style scoped lang="scss">
:deep(.ivu-input-icon) {
  color: #bbbbbb;
}

:deep(.ivu-input-word-count) {
  color: #bbbbbb;
}

.hot_imgs {
  margin: 0 15px 20px 15px;
  .type-title {
    font-size: 12px;
    color: #999;
    margin-right: 30px;
  }

  :deep(.el-radio) {
    margin-bottom: 0px;
  }
  .title {
    padding-bottom: 21px;
    color: #999;
    font-size: 12px;
  }
  .type-switch {
    display: flex;
    align-items: center;
  }
  .list-box {
    .item {
      position: relative;
      display: flex;
      background: #f9f9f9;
      align-items: center;
      padding: 16px 20px 16px 0;
      margin-bottom: 16px;
      border-radius: 3px;

      .delect-btn {
        position: absolute;
        right: -13px;
        top: -16px;

        .iconfont {
          font-size: 25px;
          color: #ccc;
        }
      }

      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        cursor: move;
      }

      .img-box {
        position: relative;
        width: 64px;
        height: 64px;

        img {
          width: 100%;
          height: 100%;
          border-radius: 3px;
        }
      }

      .info {
        flex: 1;
        margin-left: 22px;

        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;

          &:nth-last-child(1) {
            margin-bottom: 0;
          }

          .span {
            width: 40px;
            font-size: 12px;
            color: #999;
          }

          .input-box {
            flex: 1;
          }
        }
      }
    }
  }

  .add-btn {
    margin-top: 10px;

    .btn {
      width: 100%;
      height: 36px;
      border-color: #eeeeee;
      color: #666666;

      .iconfont {
        font-size: 11px;
        margin-right: 5px;
      }
    }
  }
}

.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #eeeeee;
  color: #ccc;
}

.iconfont {
  color: #dddddd;
  font-size: 16px;
}
</style>
