<template>
  <div class="hot_imgs">
    <div class="title" v-if="configData.title">
      {{ configData.title }}
    </div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="configData.list"
        group="peoples"
        handle=".move-icon"
        :item-key="swipersItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item">
            <div class="delect-btn" @click.stop="bindDelete(item, index)" v-if="!configData.isCube">
              <span class="iconfont icondel_1"></span>
            </div>
            <div class="move-icon">
              <span class="iconfont icondrag"></span>
            </div>
            <div>
              <div class="info">
                <div class="info-item">
                  <span class="span">{{ item.imgTitle }}</span>
                  <div class="img-box" @click="modalPicTap('单选', index)">
                    <img :src="item.img" alt="" v-if="item.img" />
                    <div class="upload-box" v-else><i class="el-icon-plus"></i></div>
                  </div>
                </div>
              </div>
              <div class="info">
                <div class="info-item" v-for="(infos, key) in item.info" :key="key">
                  <span class="span">{{ infos.title }}</span>
                  <div class="input-box">
                    <el-input v-model="infos.value" :placeholder="infos.tips">
                      <template #suffix><i class="el-icon-link" @click="getLink(index, key, item.info)" /></template>
                    </el-input>
                  </div>
                </div>
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
          <span class="iconfont iconjiahao"></span>添加
        </el-button>
      </div>
    </template>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import iconsPng from '@/assets/images/icons.png';
import { ref, watch, onMounted, nextTick } from 'vue';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'c_swipers_list' });

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
});

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

// vuedraggable4 item-key（数据无唯一 id，用索引）
function swipersItemKey(element) {
  return configData.value.list.indexOf(element);
}

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
  });
});

watch(
  () => props.configObj,
  (nVal, oVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
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
  if (key != item.length - 1) {
    return;
  }
  activeIndex.value = index;
  linkaddres.value.modals = true;
}
function addBox() {
  if (configData.value.list.length == 0) {
    lastObj.value.img = '';
    lastObj.value.info[0].value = '';
    configData.value.list.push(lastObj.value);
  } else {
    let obj = JSON.parse(JSON.stringify(configData.value.list[configData.value.list.length - 1]));
    obj.img = '';
    obj.info[0].value = '';
    configData.value.list.push(obj);
  }
}
// 点击图文封面
function modalPicTap(title, index) {
  activeIndex.value = index;
  modalPic.value = true;
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
    lastObj.value = configData.value.list[0];
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

  .title {
    padding-bottom: 21px;
    color: #999;
    font-size: 12px;
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
        margin-left: 19px;

        & ~ .info {
          margin-top: 13px;
        }

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
            width: 270px;
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
  border: 1px solid #eee;
  color: #ccc;
}

.iconfont {
  color: #dddddd;
  font-size: 28px;
}
</style>
